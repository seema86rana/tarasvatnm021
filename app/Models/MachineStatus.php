<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineStatus extends Model
{
    use HasFactory;

    protected $table = "machine_status";

    protected $fillable = [
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

    public static function boot()
    {
        parent::boot();

        static::deleting(function (MachineStatus $machineStatus) {
            $machineStatusLogs = MachineStatusLog::where('machine_status_id', $machineStatus->id)->get();
            if ($machineStatusLogs->isNotEmpty()) {
                $machineStatusLogs->each->delete();
            }

            $pickCals = PickCalculation::where('machine_status_id', $machineStatus->id)->get();
            if ($pickCals->isNotEmpty()) {
                $pickCals->each->delete();
            }
        });
    }
    
    public function machine() {
        return $this->belongsTo(MachineMaster::class, 'machine_id', 'id');
    }

    public function pickCal() {
        return $this->belongsTo(PickCalculation::class, 'id', 'machine_status_id');
    }

    public function machineStatusLogs() {
        return $this->hasMany(MachineStatusLog::class, 'machine_status_id', 'id');
    }
}
