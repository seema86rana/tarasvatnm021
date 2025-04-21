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
        DB::table('devices')->truncate();
        
        DB::table('devices')->insert([
            [
                'name' => '58.bf.25.23.3b.d4',
                'user_id' => 2,
                'shift' => '[{"shift_name":"Shift 1","shift_start_day":1,"shift_start_time":"8:00 AM","shift_end_day":1,"shift_end_time":"8:00 PM"},{"shift_name":"Shift 2","shift_start_day":1,"shift_start_time":"8:00 PM","shift_end_day":2,"shift_end_time":"7:55 AM"}]',
                'status' => 1, // Active status
                'created_by' => 1, // SuperAdmin User
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
