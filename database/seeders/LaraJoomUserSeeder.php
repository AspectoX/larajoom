<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class LaraJoomUserSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('larajoom_users')) return;

        DB::table('larajoom_users')->insertOrIgnore([
            'name'        => 'Administrator',
            'username'    => 'admin',
            'email'       => 'test@test.ax',
            'password'    => Hash::make('password'),
            'group_id'    => 9,
            'activated'   => 1,
            'params'      => json_encode([]),
            'registerDate' => now(),
        ]);
    }
}
