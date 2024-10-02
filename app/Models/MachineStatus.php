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
        'intime_pick',
        'shift_pick',
        'total_pick',
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
        'created_by',
        'updated_by',
    ];
    
    public $timestamps = false;
    
    public function machineMaster() {
        return $this->hasOne(MachineMaster::class, 'id', 'machine_id');
    }
}
