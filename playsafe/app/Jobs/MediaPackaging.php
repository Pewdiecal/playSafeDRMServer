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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class MediaPackaging implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $mediaContent;
    private $mediaInputPath;
    private $mediaOutputPath;
    private $dirName;
    private $encryptMedia;
    private $request;
    private $covertArtInputPath;
    private $covertArtOutputPath;
    private $contentProviderId;
    private $coverArtExt;
    private $premiumMaxRes;
    private $standardMaxRes;
    private $basicMaxRes;
    private $budgetMaxRes;
    private $premiumTrialMaxRes;
    public $timeout = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $request, string $covertArtInputPath, string $covertArtOutputPath, int $contentProviderId ,string $mediaInputPath, string $mediaOutputPath, string $dirName, string $coverArtExt)
    {
        $this->mediaInputPath = $mediaInputPath;
        $this->mediaOutputPath = $mediaOutputPath;
        $this->dirName = $dirName;
        $this->encryptMedia = $request['encryptMedia'];
        $this->premiumMaxRes = $request['premiumMaxRes'];
        $this->standardMaxRes = $request['standardMaxRes'];
        $this->basicMaxRes = $request['basicMaxRes'];
        $this->budgetMaxRes = $request['budgetMaxRes'];
        $this->premiumTrialMaxRes = $request['premiumTrialMaxRes'];
        $this->request = $request;
        $this->covertArtInputPath = $covertArtInputPath;
        $this->covertArtOutputPath = $covertArtOutputPath;
        $this->contentProviderId = $contentProviderId;
        $this->coverArtExt = $coverArtExt;
    }
    /**
     * Execute the job.
     *
     * @return void
     */


            // shell_exec("
            // ffmpeg \
            // -i /media/input/{$this->dirName}/{$this->dirName}.mp4 \
            // -filter_complex \
            // \"[0:v]split=2[v1][v5];\
            // [v1]scale=w=640:h=360[v1out];\
            // [v5]scale=w=640:h=360[v5out]\" \
            // -map [v5out] -c:v:1 libx264  -profile:v:1 high -level:v:1 5.0 -x264-params scenecut=0:open_gop=0:min-keyint=72:keyint=72 -b:v:1 6000k \
            // -map [v1out] -c:v:0 libx264  -profile:v:0 high -level:v:0 5.0 -x264-params scenecut=0:open_gop=0:min-keyint=72:keyint=72 -b:v:0 6000k \
            // -map a:0 -c:a:0 aac -b:a:0 96k -ac 2 \
            // -hls_time 1 \
            // -hls_key_info_file \"/media/output/{$this->dirName}/enc.keyinfo\" \
            // -hls_playlist_type vod \
            // -hls_segment_filename /media/output/{$this->dirName}/stream_%v/data%02d.ts \
            // -var_stream_map \"v:0,a:0 v:1,a:0\" stream_%v.m3u8 \
            // -master_pl_name \"master.m3u8\" \
            // /media/output/{$this->dirName}/{$this->dirName}.m3u8
            // ");

    public function handle()
    {
        if ($this->encryptMedia == "true") {
            $resolutions = array("144", "240", "360", "480", "720", "1080");

            $mediaLicense = new MediaLicense();
            $mediaLicense->key_id = $this->dirName;
            $mediaLicense->validity_period = now()->addDay(7);
            $mediaLicense->save();

            $mediaContent = MediaContent::create([
                'content_name' => $this->request['content_name'],
                'directory_name' => $this->dirName,
                'license_id' => $mediaLicense->license_id,
                'content_description' => $this->request['content_description'],
                'available_regions' => $this->request['available_region'],
                'is_available_offline' => $this->request['is_available_offline'],
                'content_cover_art_url' => "/publicStorage/{$this->dirName}/{$this->dirName}.{$this->coverArtExt}",
                'content_provider_id' => $this->contentProviderId,
                'genre' => $this->request['genre'],
                'max_quality_premium' => $this->premiumMaxRes,
                'max_quality_standard' => $this->standardMaxRes,
                'max_quality_basic' => $this->basicMaxRes,
                'max_quality_budget' => $this->budgetMaxRes,
                'max_quality_premiumTrial' => $this->premiumTrialMaxRes
            ]);

            shell_exec("mkdir /media/output/{$this->dirName}");
            shell_exec("mv {$this->covertArtInputPath} {$this->covertArtOutputPath}/{$this->dirName}");

            foreach ($resolutions as $resolution) {
                $bitrate = "";

                switch ($resolution) {
                    case "144":
                        $mediaContent->master_playlist_url_144p = "/publicStorage/{$this->dirName}/{$resolution}/master.m3u8";
                        $bitrate = "1M";
                        break;
                    case "240":
                        $mediaContent->master_playlist_url_240p = "/publicStorage/{$this->dirName}/{$resolution}/master.m3u8";
                        $bitrate = "1M";
                        break;
                    case "360":
                        $mediaContent->master_playlist_url_360p = "/publicStorage/{$this->dirName}/{$resolution}/master.m3u8";
                        $bitrate = "1M";
                        break;
                    case "480":
                        $mediaContent->master_playlist_url_480p = "/publicStorage/{$this->dirName}/{$resolution}/master.m3u8";
                        $bitrate = "2M";
                        break;
                    case "720":
                        $mediaContent->master_playlist_url_720p = "/publicStorage/{$this->dirName}/{$resolution}/master.m3u8";
                        $bitrate = "5M";
                        break;
                    case "1080":
                        $mediaContent->master_playlist_url_1080p = "/publicStorage/{$this->dirName}/{$resolution}/master.m3u8";
                        $bitrate = "8M";
                        break;
                }

                shell_exec("mkdir /media/output/{$this->dirName}/{$resolution}");
                shell_exec("openssl rand 16 > /media/keys/{$this->dirName}_{$resolution}.key");

                shell_exec("
                echo \"/api/media/getLicenseKey/{$this->dirName}_{$resolution}.key\" > /media/output/{$this->dirName}/{$resolution}/enc_{$resolution}.keyinfo; \
                echo \"/media/keys/{$this->dirName}_{$resolution}.key\" >> /media/output/{$this->dirName}/{$resolution}/enc_{$resolution}.keyinfo; \
                openssl rand -hex 16 >> /media/output/{$this->dirName}/{$resolution}/enc_{$resolution}.keyinfo
                ");

                $encodingResult = shell_exec("
                ffmpeg -i /media/input/{$this->dirName}/{$this->dirName}.mp4 -c:a copy \
                -vf \"scale=-2:{$resolution}\" \
                -c:v libx264 -profile:v baseline -level:v 5.0 \
                -x264-params scenecut=0:open_gop=0:min-keyint=72:keyint=72 \
                -minrate {$bitrate} -maxrate {$bitrate} -bufsize {$bitrate} -b:v {$bitrate} \
                -f hls \
                -hls_time 2 \
                -hls_key_info_file \"/media/output/{$this->dirName}/{$resolution}/enc_{$resolution}.keyinfo\" \
                -hls_playlist_type vod \
                -hls_segment_filename \"/media/output/{$this->dirName}/{$resolution}/h264_%d.ts\" \
                /media/output/{$this->dirName}/{$resolution}/master.m3u8
                ");
            }

            $mediaContent->save();
    
            var_dump("MEDIA ENCRYPTED");
            var_dump("TRANSMUX COMPLETED");
        } else {
            shell_exec("mv {$this->covertArtInputPath} {$this->covertArtOutputPath}/{$this->dirName}");
            shell_exec("mkdir /media/output/{$this->dirName}");

            $result = shell_exec("
            packager \
            input=/media/input/{$this->dirName}/{$this->dirName}.mp4,stream=audio,segment_template=/media/output/{$this->dirName}/audio/'\$'Number'\$'.aac \
            input=/media/input/{$this->dirName}/{$this->dirName}_144p.mp4,stream=video,segment_template=/media/output/{$this->dirName}/h264_144p/'\$'Number'\$'.ts \
            input=/media/input/{$this->dirName}/{$this->dirName}_360p.mp4,stream=video,segment_template=/media/output/{$this->dirName}/h264_360p/'\$'Number'\$'.ts \
            input=/media/input/{$this->dirName}/{$this->dirName}_480p.mp4,stream=video,segment_template=/media/output/{$this->dirName}/h264_480p/'\$'Number'\$'.ts \
            input=/media/input/{$this->dirName}/{$this->dirName}_720p.mp4,stream=video,segment_template=/media/output/{$this->dirName}/h264_720p/'\$'Number'\$'.ts \
            input=/media/input/{$this->dirName}/{$this->dirName}_1080p.mp4,stream=video,segment_template=/media/output/{$this->dirName}/h264_1080p/'\$'Number'\$'.ts \
            --hls_master_playlist_output {$this->mediaOutputPath}/{$this->dirName}/{$this->dirName}_master.m3u8
            ");
            var_dump("TRANSMUX COMPLETED");

            $mediaLicense = new MediaLicense();
            $mediaLicense->key_id = null;
            $mediaLicense->validity_period = null;
            $mediaLicense->save();

            $mediaContent = MediaContent::create([
                'content_name' => $this->request['content_name'],
                'directory_name' => $this->dirName,
                'license_id' => $mediaLicense->license_id,
                'content_description' => $this->request['content_description'],
                'available_regions' => $this->request['available_region'],
                'is_available_offline' => $this->request['is_available_offline'],
                'content_cover_art_url' => "/publicStorage/{$this->dirName}/{$this->dirName}.{$this->coverArtExt}",
                'content_provider_id' => $this->contentProviderId,
                'master_playlist_url' => "/publicStorage/{$this->dirName}/master.m3u8",
                'genre' => $this->request['genre']
            ]);
        }

        var_dump("View at: http://localhost:8000/publicStorage/{$this->dirName}/master.m3u8");

        Storage::disk('local')->deleteDirectory($this->dirName);
    }
}
