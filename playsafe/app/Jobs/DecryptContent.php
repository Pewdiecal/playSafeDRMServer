<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DecryptContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $contentDirPath;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $contentDirPath)
    {
        $this->contentDirPath = $contentDirPath;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $result = shell_exec("
            packager \
            input=/media/input/{$this->dirName}/{$this->dirName}.mp4,stream=audio,segment_template=/media/output/{$this->dirName}/audio/'\$'Number'\$'.aac \
            input={$this->contentDirPath}/h264_144p.mp4,stream=video,segment_template=/media/output/{$this->dirName}/h264_144p/'\$'Number'\$'.ts \
            input={$this->contentDirPath}/h264_360p.mp4,stream=video,segment_template=/media/output/{$this->dirName}/h264_360p/'\$'Number'\$'.ts \
            input={$this->contentDirPath}/h264_480p.mp4,stream=video,segment_template=/media/output/{$this->dirName}/h264_480p/'\$'Number'\$'.ts \
            input={$this->contentDirPath}/h264_720p.mp4,stream=video,segment_template=/media/output/{$this->dirName}/h264_720p/'\$'Number'\$'.ts \
            input={$this->contentDirPath}/_1080p.mp4,stream=video,segment_template=/media/output/{$this->dirName}/h264_1080p/'\$'Number'\$'.ts \
            --hls_master_playlist_output /media/output/{$this->dirName}/{$this->dirName}_master.m3u8
            ");
    }
}
