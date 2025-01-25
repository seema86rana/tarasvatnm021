<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempMachineStatus extends Model
{
    use HasFactory;

    protected $table = "temp_machine_status";

    protected $fillable = [
        'machine_status_id',
        'machine_log_id',
        'machine_id',
        'active_machine',
        'speed',
        'status',
        'no_of_stoppage',
        'last_stop',
        'last_running',
        'total_running',
        'total_time',
        'efficiency',
        'device_datetime',
        'machine_datetime',
        'shift_date',
        'shift_name',
        'shift_start_datetime',
        'shift_end_datetime',
    ];
    
    public $timestamps = true;

    public function machine_status()
    {
        return $this->belongsTo(MachineStatus::class, 'machine_status_id', 'id');
    }

    public function machine_log()
    {
        return $this->belongsTo(MachineLog::class, 'machine_log_id', 'id');
    }

    public function machine()
    {
        return $this->belongsTo(MachineMaster::class, 'machine_id', 'id');
    }
}
