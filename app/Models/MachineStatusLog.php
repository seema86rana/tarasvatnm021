<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineStatusLog extends Model
{
    use HasFactory;

    protected $table = "machine_status_logs";

    protected $fillable = [
        'machine_status_id',
        'machine_log_id',
        'machine_id',
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

    public function machineStatus() {
        return $this->belongsTo(MachineStatus::class, 'machine_status_id', 'id');
    }

    public function machineMasterLog() {
        return $this->belongsTo(MachineMasterLog::class, 'machine_log_id', 'id');
    }

    public function machine() {
        return $this->belongsTo(MachineMaster::class, 'machine_id', 'id');
    }
}
