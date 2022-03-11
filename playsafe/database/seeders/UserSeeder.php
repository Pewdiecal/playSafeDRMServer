<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_records')->insert([
            'account_id' => 1,
            'username' => 'user1',
            'email' => 'user1@emil.com',
            'password' => Hash::make('12345'),
        ]);
    }
}
