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

class MediaEncoding implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $mediaInputPath;
    private $dirName;
    public $timeout = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $mediaInputPath, string $dirName)
    {
        $this->dirName = $dirName;
        $this->mediaInputPath = $mediaInputPath;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // ffmpeg -i {$this->mediaInputPath} -c:a copy \
        // -vf \"scale=-2:144\" \
        // -c:v libx264 -profile:v baseline -level:v 3.0 \
        // -x264-params scenecut=0:open_gop=0:min-keyint=72:keyint=72 \
        // -minrate 600k -maxrate 600k -bufsize 600k -b:v 600k \
        // -f hls \
        // -hls_time 2 \
        // -hls_playlist_type vod \
        // -hls_flags independent_segments \
        // -hls_segment_type mpegts \
        // -hls_segment_filename /media/output/{$this->dirName}/stream_%v/data%02d.ts \
        // /media/output/{$this->dirName}/master.m3u8
        $encodingResult = shell_exec("
        ffmpeg -i {$this->mediaInputPath} \
        -s 1920x1080 -c:a aac -c:v libx264 /media/input/{$this->dirName}/{$this->dirName}_1080p.mp4 \
        -s 1280x720 -c:a aac -c:v libx264 /media/input/{$this->dirName}/{$this->dirName}_720p.mp4 \
        -s 640x480 -c:a aac -c:v libx264 /media/input/{$this->dirName}/{$this->dirName}_480p.mp4 \
        -s 480x360 -c:a aac -c:v libx264 /media/input/{$this->dirName}/{$this->dirName}_360p.mp4 \
        -s 320x240 -c:a aac -c:v libx264 /media/input/{$this->dirName}/{$this->dirName}_240p.mp4
        ");
    }
}
