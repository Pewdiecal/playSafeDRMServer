<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('account_details_records')->insert([
            'registered_region' => 'MY',
            'max_streaming_quality' => '1080p',
            'subscribtion_status' => 'active',
            'downloaded_content_qty' => 10,
            'total_streaming_hours' => 10,
            'loggedIn_device_num' => 10
        ]);
    }
}
