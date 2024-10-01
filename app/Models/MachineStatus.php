<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineStatus extends Model
{
    use HasFactory;

    protected $table = "machine_status";

    protected $fillable = [
        'user_id',
        'device_id',
        'node_id',
        'machine_id',
        'speed',
        'total_pick',
        'total_pick_shift_wise',
        'no_of_stoppage',
        'shift_stop',
        'shift_running',
        'shift_time',
        'shift_efficiency',
        'total_stop',
        'total_running',
        'total_time',
        'total_efficiency',
        'shift_name',
        'shift_start_datetime',
        'shift_end_datetime',
        'machine_date',
        'status',
        'created_by',
        'updated_by',
    ];
    
    public $timestamps = false;
    
    public function machineMaster() {
        return $this->hasOne(MachineMaster::class, 'id', 'machine_id');
    }
}
