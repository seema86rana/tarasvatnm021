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
        DB::table('roles')->insert([
            [
                'name' => 'SuperAdmin',
                'status' => 1, // Active status
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),               
            ],
            [
                'name' => 'Admin',
                'status' => 1, // Active status
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),               
            ],
            [
                'name' => 'User',
                'status' => 1, // Active status
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
