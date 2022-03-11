<?php

namespace App\Jobs;

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

    private $mediaInputPath;
    private $mediaOutputPath;
    private $dirName;
    public $timeout = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $mediaInputPath, string $mediaOutputPath, string $dirName)
    {
        $this->mediaInputPath = $mediaInputPath;
        $this->mediaOutputPath = $mediaOutputPath;
        $this->dirName = "JcU76EHqybyDPdptEw2wmjhH542gOBUbinTXjyiv";
    }

    private function keyID() {
        $keyID = \substr(\bin2hex(Hash::make(Str::uuid()->toString())), rand(0, 27), 32);
        return $keyID;
    }

    private function encKeyGen() {
        // Generate AES-128-CBC key from 16 string char.
        $randomStr = Str::random(4);
        $randomInt = rand(100,999);
        $currentSystemTime = \Carbon\Carbon::now()->toDateTimeString('second');
        $rawStr = $randomStr.$randomInt.$currentSystemTime;
        $hashStr = Hash::make($rawStr);
        $privateKey = \substr(\bin2hex($hashStr), rand(0, 27), 32);
        return $privateKey;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $audioKeyID = $this->keyID();
        $audioKey = $this->encKeyGen();

        $sdKeyID = $this->keyID();
        $sdKey = $this->encKeyGen();

        $hdKeyID = $this->keyID();
        $hdKey = $this->encKeyGen();

        // input=/media/input/{$this->dirName}/{$this->dirName}_360p.mp4,stream=video,segment_template=/media/output/{$this->dirName}/h264_360p/'\$'Number'\$'.ts,drm_label=SD \
        // input=/media/input/{$this->dirName}/{$this->dirName}_480p.mp4,stream=video,segment_template=/media/output/{$this->dirName}/h264_480p/'\$'Number'\$'.ts,drm_label=SD \
        // input=/media/input/{$this->dirName}/{$this->dirName}_720p.mp4,stream=video,segment_template=/media/output/{$this->dirName}/h264_720p/'\$'Number'\$'.ts,drm_label=HD \
        // input=/media/input/{$this->dirName}/{$this->dirName}_1080p.mp4,stream=video,segment_template=/media/output/{$this->dirName}/h264_1080p/'\$'Number'\$'.ts,drm_label=HD \
        
        // Execute shaka packager command.
        // $result = shell_exec("
        // packager \
        // input=/media/input/{$this->dirName}/{$this->dirName}.mp4,stream=audio,segment_template=/media/output/{$this->dirName}/audio/'\$'Number'\$'.aac,drm_label=SD \
        // input=/media/input/{$this->dirName}/{$this->dirName}.mp4,stream=video,segment_template=/media/output/{$this->dirName}/h264_144p/'\$'Number'\$'.ts,drm_label=SD \
        // --enable_raw_key_encryption \
        // --keys label=AUDIO:key_id={$sdKeyID}:key={$sdKey},label=SD:key_id={$sdKeyID}:key={$sdKey} \
        // --clear_lead 0 \
        // --protection_scheme cbcs \
        // --hls_master_playlist_output /media/output/{$this->dirName}/{$this->dirName}_master.m3u8
        // ");

        // shell_exec("echo \"{$sdKey}\" > /media/output/{$this->dirName}/key.bin");

        // var_dump("audioKeyID: {$audioKeyID}");
        // var_dump("audioKey: {$audioKey}");

        // var_dump("sdKeyID: {$sdKeyID}");
        // var_dump("sdKey: {$sdKey}");

        shell_exec("mkdir /media/output/{$this->dirName}; mkdir /media/output/{$this->dirName}/h264_360p");
        shell_exec("echo \"{$sdKey}\" > /media/output/{$this->dirName}/media.key");

        shell_exec("
        echo \"http://localhost:8000/publicStorage/{$this->dirName}/media.key\" > /media/output/{$this->dirName}/enc.keyinfo; \
        echo \"/media/output/{$this->dirName}/media.key\" >> /media/output/{$this->dirName}/enc.keyinfo; \
        echo \"{$audioKey}\" >> /media/output/{$this->dirName}/enc.keyinfo
        ");

        shell_exec("
        ffmpeg -y \
        -i /media/input/{$this->dirName}/{$this->dirName}_360p.mp4 \
        -hls_time 2 \
        -hls_key_info_file \"/media/output/{$this->dirName}/enc.keyinfo\" \
        -hls_playlist_type vod \
        -hls_segment_filename \"/media/output/{$this->dirName}/h264_360p/h264_360p_%d.ts\" \
        /media/output/{$this->dirName}/{$this->dirName}_master.m3u8");

        var_dump("View at: http://localhost:8000/publicStorage/{$this->dirName}/{$this->dirName}_master.m3u8");

        //Storage::disk('local')->deleteDirectory($this->dirName);
    }
}
