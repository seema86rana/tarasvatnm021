<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Truncate the table
        DB::table('roles')->truncate();

        // Seed the table with data
        DB::table('roles')->insert([
            [
                'name' => 'Admin',
                'permission' => json_encode([]),
                'status' => 1, // Active status
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'User',
                'permission' => json_encode(['dashboard', 'profile', 'machines']),
                'status' => 1, // Active status
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
