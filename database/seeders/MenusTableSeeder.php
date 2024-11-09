<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MenusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Truncate the table
        DB::table('menus')->truncate();

        // Seed the table with data
        DB::table('menus')->insert([
            [
                'name' => 'Dashboard', // 1
                'parent_id' => 0,
                'route' => 'dashboard',
                'icon' => '<i class="icon-home4"></i>',
                'position' => 1,
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'User', // 2
                'parent_id' => 0,
                'route' => 'users',
                'icon' => '<i class="icon-users"></i>',
                'position' => 2,
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Device', // 3
                'parent_id' => 0,
                'route' => 'devices',
                'icon' => '<i class="icon-stack2"></i>',
                'position' => 3,
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Setting', // 4
                'parent_id' => 0,
                'route' => 'settings',
                'icon' => '<i class="icon-cog3"></i>',
                'position' => 4,
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Role & permission', // 5
                'parent_id' => 4, // Setting
                'route' => 'roles',
                'icon' => '<i class="fa fa-user-times"></i>',
                'position' => 1,
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Report', // 6
                'parent_id' => 0,
                'route' => 'reports',
                'icon' => '<i class="icon-clippy"></i>',
                'position' => 5,
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
