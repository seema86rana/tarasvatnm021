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

        /* 
        for ($i=0; $i < 5000000; $i++) { 
            DB::table('temp_machine_status')->insert([
                'machine_status_id' => 3,
                'machine_log_id' => 2,
                'machine_id' => 75,
                'active_machine' => 1,
                'speed' => 160,
                'status' => 1,
                'no_of_stoppage' => 1,
                'last_stop' => 60.00,
                'last_running' => 2.00,
                'total_running' => 62.00,
                'total_time' => 62.00,
                'efficiency' => 100.00,
                'device_datetime' => date('Y-m-d H:i:s'),
                'machine_datetime' => date('Y-m-d H:i:s'),
                'shift_date' => date('Y-m-d'),
                'shift_name' => 'shift 1',
                'shift_start_datetime' => date('Y-m-d H:i:s'),
                'shift_end_datetime' => date('Y-m-d H:i:s'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
        */

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
                'route' => '#1',
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
                'name' => 'Reports', // 6
                'parent_id' => 0,
                'route' => '#2',
                'icon' => '<i class="fa fa-file-text"></i>',
                'position' => 5,
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'View Report Data', // 7
                'parent_id' => 6,
                'route' => 'view-reports',
                'icon' => '<i class="fa fa-eye"></i>',
                'position' => 1,
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Remove Report Data', // 8
                'parent_id' => 6,
                'route' => 'clear-reports',
                'icon' => '<i class="fa fa-trash"></i>',
                'position' => 2,
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
