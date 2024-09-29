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
        'avg_speed',
        'total_pick',
        'avg_total_pick',
        'total_pick_shift_wise',
        'efficiency',
        'no_of_stoppage',
        'last_stop',
        'last_running',
        'total_running',
        'shift_name',
        'shift_start_datetime',
        'shift_end_datetime',
        'machine_date',
        'status',
        'created_by',
        'updated_by',
    ];
    
    public function machineMaster() {
        return $this->hasOne(MachineMaster::class, 'id', 'machine_id');
    }
}
