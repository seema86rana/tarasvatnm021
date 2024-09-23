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
        /* DB::table('devices')->insert([
            [
                'name' => '123.125.101.121.1.1',
                'user_id' => 2,
                'shift' => '[{"shift_name":"Shift 1","shift_start":"10:00 AM","shift_end":"3:00 PM"},{"shift_name":"Shift 2","shift_start":"3:00 PM","shift_end":"4:30 PM"},{"shift_name":"Shift 3","shift_start":"4:00 PM","shift_end":"5:00 PM"}]',
                'status' => 1, // Active status
                'created_by' => 1, // SuperAdmin User
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]); */
    }
}
