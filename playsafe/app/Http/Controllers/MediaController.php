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
        // Handle file upload.
        $file = $request->file('rawMediaFile');
        $dirName = pathinfo($file->hashName())['filename'];
        // Includes file extension.
        $path = $file->store($dirName);

        // Arguments for shaka packager.
        $mediaInputPath = '/media/input/'.$path;
        $mediaOutputPath = '/media/output/'.$path;

        // Dispatch media encoding job.
        //MediaEncoding::dispatch($mediaInputPath, $dirName);

        // Dispatch media transmuxing job.
        MediaPackaging::dispatch($mediaInputPath, $mediaOutputPath, $dirName);

        // if ($user = auth()->user()) {
        //     $validation = Validator::make($request->all(), [
        //         'content_name' => 'required',
        //         'file_format' => 'required',
        //         'content_description' => 'required',
        //         'available_region' => 'required',
        //         'is_available_offline' => 'required'
        //     ]);
    
        //     if ($validation->fails()) {
        //         return response()->json($validation->errors()->toArray(), 400);
        //     }
            
        //     $mediaLicense = new MediaLicense();
        //     $mediaLicense->private_key = "adasd";
        //     $mediaLicense->public_key = "asdasd";
        //     $mediaLicense->validity_period = now();
        //     $mediaLicense->save();

        //     $mediaContent = MediaContent::create([
        //         'content_name' => $request->input('content_name'),
        //         'file_format' => $request->input('file_format'),
        //         'content_description' => $request->input('content_description'),
        //         'available_regions' => $request->input('available_region'),
        //         'is_available_offline' => $request->input('is_available_offline')
        //     ]);

        //     $mediaLicense->license_id = $mediaLicense->license_id;

        //     $path = $request->file('media')->store('avatars');

            // if ($user->is_content_provider) {
            //     $mediaLicense = MediaLicense::create();
            //     MediaContent::create([
            //         'content_name' => $request->input('content_name'),
            //         'file_format' => $request->input('file_format'),
            //         'content_description' => $request->input('content_description'),
            //         'available_regions' => $request->input('available_region'),
            //         'is_available_offline' => $request->input('is_available_offline'),
            //     ]);
            // } else {
            //     return response()->json(["error" => "Unauthorized"], 401);
            // }
        //}
        //return response()->json(["error" => "Unauthorized"], 401);
    }

    public function removeContent(Request $request) {

    }

    public function editContentMetadata(Request $request) {

    }

    public function getContentList(Request $request) {

    }

    public function getLicenseInfo(Request $request) {

    }

    public function getContentMetadata(Request $request) {

    }
}
