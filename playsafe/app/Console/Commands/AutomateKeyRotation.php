<?php

namespace App\Console\Commands;

use App\Jobs\RotateKey;
use App\Models\MediaContent;
use App\Models\MediaLicense;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AutomateKeyRotation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'automate:keyRotation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automates the process of key rotation.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dateToday = now()->toDateString();
        $mediaLicenses = MediaLicense::whereDate('validity_period', '=' ,$dateToday)->get();
        var_dump($mediaLicenses);
        foreach($mediaLicenses as $license) {
            $content = MediaContent::where('license_id', $license->license_id)->first();
            $dirNameToDelete = $content->directory_name;

            // Delete all old keys for all resolutions.
            $resolutions = array("144", "240", "360", "480", "720", "1080");
            foreach ($resolutions as $resolution) {
                Storage::disk('local')->delete('keys/'.$dirNameToDelete.'_{$resolution}.key');

                 // Delete existing transmuxed file.
                Storage::disk('local')->deleteDirectory('mediaContents/'.$dirNameToDelete.'/'.$resolution);
            }

            RotateKey::dispatch($content->content_id);
        }
    }
}
