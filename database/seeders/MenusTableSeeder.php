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
                'name' => 'Node', // 4
                'parent_id' => 0,
                'route' => 'nodes',
                'icon' => '<i class="icon-blog"></i>',
                'position' => 4,
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Machine', // 5
                'parent_id' => 0,
                'route' => 'machines',
                'icon' => '<i class="fa fa-cogs"></i>',
                'position' => 5,
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Setting', // 6
                'parent_id' => 0,
                'route' => 'settings',
                'icon' => '<i class="icon-cog3"></i>',
                'position' => 6,
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Role & permission', // 7
                'parent_id' => 6, // Setting
                'route' => 'roles',
                'icon' => '<i class="fa fa-user-times"></i>',
                'position' => 1,
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
