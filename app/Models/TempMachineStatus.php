<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempMachineStatus extends Model
{
    use HasFactory;

    protected $table = "temp_machine_status";

    protected $fillable = [
        'user_id',
        'device_id',
        'node_id',
        'machine_id',
        'speed',
        'no_of_stoppage',
        'last_stop',
        'last_running',
        'total_running',
        'total_time',
        'efficiency',
        'shift_name',
        'shift_start_datetime',
        'shift_end_datetime',
        'machine_date',
        'status',
        'machine_status_id',
        'machine_log',
        'created_by',
        'updated_by',
    ];
    
    public $timestamps = false;
}
