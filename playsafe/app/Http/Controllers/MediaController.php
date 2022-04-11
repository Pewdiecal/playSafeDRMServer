<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Jobs\MediaEncoding;
use App\Jobs\MediaPackaging;
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
                    'contentId' => 'required',
                    'contentName' => 'required',
                    'rawMediaFile' => 'required',
                    'coverArt' => 'required',
                    'contentDescription' => 'required',
                    'availableRegion' => 'required',
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
            $contentMaxStreamingQualityForSub = MediaContent::select("max_quality_".$subscriptionType)->where('content_id', $contentId)->first()->max_quality_basic;
            $masterPlaylistUrl = MediaContent::select('master_playlist_url_'.$contentMaxStreamingQualityForSub)->where('content_id', $contentId)->first()->toJson();
            return $masterPlaylistUrl;
        } else {
            return response()->json(["error" => "Unauthorized"], 401);
        }
    }

    public function getLicenseKey(int $licenseId) {

    }

    public function decryptContent(int $contentId) {
        // if ($user = auth()->user()) {
        //     if ($user->is_content_provider) {
        //         $mediaContent = MediaContent::where('content_id', $contentId);

        //         MediaLicense::destroy($mediaContent->license_id);

        //         $contentDirPath = '/media/output/'.$mediaContent->directory_name;
                
        //     } else {
        //         return response()->json(["error" => "Unauthorized"], 401);
        //     }
        // } else {
        //     return response()->json(["error" => "Unauthorized"], 401);
        // }

        return response()->json(["Decryption Status:" => "Completed"], 200);
    }
}
