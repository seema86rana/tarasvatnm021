<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TempInsertMultipleTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('devices')->insert([
            [
                'name' => '10.06.1c.82.41.34',
                'user_id' => 2,
                'shift' => '[{"shift_name":"Shift 1","shift_start_day":1,"shift_start_time":"6:00 AM","shift_end_day":1,"shift_end_time":"10:30 AM"},{"shift_name":"Shift 2","shift_start_day":1,"shift_start_time":"11:00 AM","shift_end_day":1,"shift_end_time":"3:00 PM"},{"shift_name":"Shift 3","shift_start_day":1,"shift_start_time":"3:00 PM","shift_end_day":2,"shift_end_time":"1:00 AM"},{"shift_name":"Shift 4","shift_start_day":2,"shift_start_time":"1:30 AM","shift_end_day":2,"shift_end_time":"5:30 AM"}]',
                'status' => 1, // Active status
                'created_by' => 1, // SuperAdmin User
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'c8.2e.18.f7.41.54',
                'user_id' => 2,
                'shift' => '[{"shift_name":"Shift 1","shift_start_day":1,"shift_start_time":"6:00 AM","shift_end_day":1,"shift_end_time":"10:30 AM"},{"shift_name":"Shift 2","shift_start_day":1,"shift_start_time":"11:00 AM","shift_end_day":1,"shift_end_time":"3:00 PM"},{"shift_name":"Shift 3","shift_start_day":1,"shift_start_time":"3:00 PM","shift_end_day":2,"shift_end_time":"1:00 AM"},{"shift_name":"Shift 4","shift_start_day":2,"shift_start_time":"1:30 AM","shift_end_day":2,"shift_end_time":"5:30 AM"}]',
                'status' => 1, // Active status
                'created_by' => 1, // SuperAdmin User
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'c8.2e.18.c0.b9.c0',
                'user_id' => 2,
                'shift' => '[{"shift_name":"Shift 1","shift_start_day":1,"shift_start_time":"6:00 AM","shift_end_day":1,"shift_end_time":"10:30 AM"},{"shift_name":"Shift 2","shift_start_day":1,"shift_start_time":"11:00 AM","shift_end_day":1,"shift_end_time":"3:00 PM"},{"shift_name":"Shift 3","shift_start_day":1,"shift_start_time":"3:00 PM","shift_end_day":2,"shift_end_time":"1:00 AM"},{"shift_name":"Shift 4","shift_start_day":2,"shift_start_time":"1:30 AM","shift_end_day":2,"shift_end_time":"5:30 AM"}]',
                'status' => 1, // Active status
                'created_by' => 1, // SuperAdmin User
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
