<?php

namespace App\Jobs;

use App\Models\MediaContent;
use App\Models\MediaLicense;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RotateKey implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $contentId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $contentId)
    {
        $this->contentId = $contentId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $resolutions = array("144", "240", "360", "480", "720", "1080");
        $dirName = MediaContent::select('directory_name')->where('content_id', $this->contentId)->first()->directory_name;

        foreach ($resolutions as $resolution) {
            $bitrate = "";

            switch ($resolution) {
                case "144":
                    $bitrate = "1M";
                    break;
                case "240":
                    $bitrate = "1M";
                    break;
                case "360":
                    $bitrate = "1M";
                    break;
                case "480":
                    $bitrate = "2M";
                    break;
                case "720":
                    $bitrate = "5M";
                    break;
                case "1080":
                    $bitrate = "8M";
                    break;
            }

            shell_exec("mkdir /media/output/{$dirName}/{$resolution}");
            shell_exec("openssl rand 16 > /media/keys/{$dirName}_{$resolution}.key");

            shell_exec("
            echo \"/api/media/getLicenseKey/{$dirName}_{$resolution}.key\" > /media/output/{$dirName}/{$resolution}/enc_{$resolution}.keyinfo; \
            echo \"/media/keys/{$dirName}_{$resolution}.key\" >> /media/output/{$dirName}/{$resolution}/enc_{$resolution}.keyinfo; \
            openssl rand -hex 16 >> /media/output/{$dirName}/{$resolution}/enc_{$resolution}.keyinfo
            ");

            $encodingResult = shell_exec("
            ffmpeg -i /media/input/{$dirName}/{$dirName}.mp4 -c:a copy \
            -vf \"scale=-2:{$resolution}\" \
            -c:v libx264 -profile:v baseline -level:v 5.0 \
            -x264-params scenecut=0:open_gop=0:min-keyint=72:keyint=72 \
            -minrate {$bitrate} -maxrate {$bitrate} -bufsize {$bitrate} -b:v {$bitrate} \
            -f hls \
            -hls_time 5 \
            -hls_key_info_file \"/media/output/{$dirName}/{$resolution}/enc_{$resolution}.keyinfo\" \
            -hls_playlist_type vod \
            -hls_segment_filename \"/media/output/{$dirName}/{$resolution}/h264_%d.ts\" \
            /media/output/{$dirName}/{$resolution}/master.m3u8
            ");
        }

        $licenseId = MediaContent::select('license_id')->where('content_id', $this->contentId)->first()->license_id;
        $mediaLicense = MediaLicense::where('license_id', $licenseId)->first();
        $mediaLicense->validity_period = now()->addDay(30);
        $mediaLicense->save(); 
    }
}
