<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Jobs\MediaEncoding;
use App\Jobs\MediaPackaging;
use App\Jobs\RotateKey;
use App\Models\AccountDetails;
use App\Models\MediaContent;
use App\Models\MediaLicense;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Storage;
use Validator;

class MediaController extends Controller
{
    public function uploadAndPackage(Request $request) {
        if ($user = auth()->user()) {
            $validation = Validator::make($request->all(), [
                'contentName' => 'required',
                'rawMediaFile' => 'required|mimes:mp4',
                'coverArt' => 'required|mimes:jpg,jpeg,png',
                'contentDescription' => 'required',
                'availableRegion' => 'required',
                'isAvailableOffline' => 'required',
                'encryptMedia' => 'required',
                'premiumMaxRes' => 'required',
                'standardMaxRes' => 'required',
                'basicMaxRes' => 'required',
                'budgetMaxRes' => 'required',
                'premiumTrialMaxRes' => 'required'
            ]);
    
            if ($validation->fails()) {
                return response()->json($validation->errors()->toArray(), 400);
            }

            // Handle file upload.
            $coverArt = $request->file('coverArt');
            $mediaFile = $request->file('rawMediaFile');

            // dir Name.
            $dirName = pathinfo($coverArt.$mediaFile->hashName())['filename'];

            // Includes file extension.
            $coverArtExt = $coverArt->getClientOriginalExtension();
            $mediaFileExt = $mediaFile->getClientOriginalExtension();
            $coverArtPath = $coverArt->storeAs($dirName, $dirName.".{$coverArtExt}");
            $mediaPath = $mediaFile->storeAs($dirName, $dirName.".{$mediaFileExt}");

            // Arguments for shaka packager.
            $mediaInputPath = '/media/input/'.$mediaPath;
            $mediaOutputPath = '/media/output/';

            $covertArtInputPath = '/media/input/'.$coverArtPath;
            $coverArtOutputPath = '/media/output/';

            if ($user->is_content_provider) {
                // Dispatch media transmuxing and encoding job.
                MediaPackaging::dispatch(array('encryptMedia' => $request->input('encryptMedia'),
                                                'content_name' => $request->input('contentName'),
                                                'content_description' => $request->input('contentDescription'),
                                                'available_region' => $request->input('availableRegion'),
                                                'is_available_offline' => $request->input('isAvailableOffline'),
                                                'premiumMaxRes' => $request->input('premiumMaxRes'),
                                                'standardMaxRes' => $request->input('standardMaxRes'),
                                                'basicMaxRes' => $request->input('basicMaxRes'),
                                                'budgetMaxRes' => $request->input('budgetMaxRes'),
                                                'premiumTrialMaxRes' => $request->input('premiumTrialMaxRes'),
                                                'genre' => $request->input('genre')
                                        ), $covertArtInputPath, $coverArtOutputPath, $user->user_id, $mediaInputPath, $mediaOutputPath, $dirName, $coverArtExt);
            } else {
                return response()->json(["error" => "Unauthorizeds"], 401);
            }
        } else {
            return response()->json(["error" => "Unauthorized"], 401);
        }
    }

    public function removeContent(int $contentId) {
        if ($user = auth()->user()) {
            if ($user->is_content_provider) {
                $dirNameToDelete = MediaContent::select('directory_name')->where('content_id', $contentId)->first()->directory_name;
                Storage::disk('local')->deleteDirectory('mediaContents/'.$dirNameToDelete);
                Storage::disk('local')->deleteDirectory('uploadedMedia/'.$dirNameToDelete);

                // Delete all old keys for all resolutions.
                $resolutions = array("144", "240", "360", "480", "720", "1080");
                foreach ($resolutions as $resolution) {
                    Storage::disk('local')->delete('keys/'.$dirNameToDelete.'_{$resolution}.key');
                }

                $licenseId = MediaContent::where('content_id', $contentId)->first()->license_id;
                MediaContent::destroy($contentId);
                MediaLicense::destroy($licenseId);
            } else {
                return response()->json(["error" => "Unauthorized"], 401);
            }
        } else {
            return response()->json(["error" => "Unauthorized"], 401);
        }
    }

    public function editContentMetadata(Request $request) {
        if ($user = auth()->user()) {
            if ($user->is_content_provider) {
                $validation = Validator::make($request->all(), [
                    'content_id' => 'required',
                    'content_name' => 'required',
                    'genre' => 'required',
                    'content_description' => 'required',
                    'available_regions' => 'required',
                    'max_quality_premium' => 'required',
                    'max_quality_standard' => 'required',
                    'max_quality_basic' => 'required',
                    'max_quality_budget' => 'required',
                    'max_quality_premiumTrial' => 'required'
                ]);

                if ($validation->fails()) {
                    return response()->json($validation->errors()->toArray(), 400);
                }

                $mediaContent = MediaContent::find($request->input('content_id'));
                $mediaContent->content_name = $request->input('content_name');
                $mediaContent->genre = $request->input('genre');
                $mediaContent->available_regions = $request->input('available_regions');
                $mediaContent->content_description = $request->input('content_description');
                $mediaContent->max_quality_premium = $request->input('max_quality_premium');
                $mediaContent->max_quality_standard = $request->input('max_quality_standard');
                $mediaContent->max_quality_basic = $request->input('max_quality_basic');
                $mediaContent->max_quality_budget = $request->input('max_quality_budget');
                $mediaContent->max_quality_premiumTrial = $request->input('max_quality_premiumTrial');
                $mediaContent->save();
            } else {
                return response()->json(["error" => "Unauthorized"], 401);
            }
        } else {
            return response()->json(["error" => "Unauthorized"], 401);
        }
    }

    public function getProviderContentList(Request $request) {
        if ($user = auth()->user()) {
            if ($user->is_content_provider) {
                $contents = MediaContent::where('content_provider_id', $user->user_id)->get()->toJson();
                return $contents;
            } else {
                return response()->json(["error" => "Unauthorized"], 401);
            }
        } else {
            return response()->json(["error" => "Unauthorized"], 401);
        }
    }

    public function getContentList(string $country) {
        if ($user = auth()->user()) {
            $userAccountId = $user->account_id;
            $subscriptionType = AccountDetails::select('subscribtion_status')->where('account_id', $userAccountId);

            $contents = MediaContent::select(
                'content_id', 
                'content_name', 
                'license_id',
                'genre', 
                'content_description', 
                'content_cover_art_url')->where('available_regions', $country)->get()->toJson();    
            return $contents;
        } else {
            return response()->json(["error" => "Unauthorized"], 401);
        }
    }

    public function getMasterPlaylistUrl(int $contentId) {
        if ($user = auth()->user()) {
            $userAccountId = $user->account_id;
            $subscriptionType = AccountDetails::select('subscribtion_status')->where('account_id', $userAccountId)->first()->subscribtion_status;
            $contentMaxStreamingQualityForSub = MediaContent::select("max_quality_".$subscriptionType)->where('content_id', $contentId)->first();
            $MaxQuality = json_decode($contentMaxStreamingQualityForSub, true);
            $masterPlaylistUrl = MediaContent::select("max_quality_".$subscriptionType, 'master_playlist_url_'.$MaxQuality["max_quality_".$subscriptionType])->where('content_id', $contentId)->first()->toJson();
            return $masterPlaylistUrl;
        } else {
            return response()->json(["error" => "Unauthorized"], 401);
        }
    }

    public function getLicenseKey(string $keyPathname) {
        if ($user = auth()->user()) {
            if ($keyFile = Storage::disk('local')->get('keys/'.$keyPathname)) {
                return $keyFile;
            } else {
                return response()->json(["error" => "Key not found"], 404);
            }
        } else {
            return response()->json(["error" => "Unauthorized"], 401);
        }
    }

    public function rotateKey(int $contentId) {
        if ($content = MediaContent::select('directory_name')->where('content_id', $contentId)->first()) {
            $dirNameToDelete = $content->directory_name;

            // Delete all old keys for all resolutions.
            $resolutions = array("144", "240", "360", "480", "720", "1080");
            foreach ($resolutions as $resolution) {
                Storage::disk('local')->delete('keys/'.$dirNameToDelete.'_{$resolution}.key');

                 // Delete existing transmuxed file.
                Storage::disk('local')->deleteDirectory('mediaContents/'.$dirNameToDelete.'/'.$resolution);
            }

            // Dispatch key rotation job to queue.
            RotateKey::dispatch($contentId);

            return response()->json(["status" => "Key rotation request success."], 200);
        }

        return response()->json(["error" => "Content id not found"], 404);
    }
}
