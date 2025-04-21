<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineMaster extends Model
{
    use HasFactory;

    protected $table = "machine_master";

    protected $fillable = [
        'node_id',
        'name',
        'display_name',
        'priority',
        'status',
        'current_status',
    ];

    public $timestamps = true;

    public static function boot()
    {
        parent::boot();

        static::deleting(function (MachineMaster $machineMaster) {
            $machineMasterLogs = MachineMasterLog::where('machine_id', $machineMaster->id)->get();
            if ($machineMasterLogs->isNotEmpty()) {
                $machineMasterLogs->each->delete();
            }

            $machineStatuses = MachineStatus::where('machine_id', $machineMaster->id)->get();
            if ($machineStatuses->isNotEmpty()) {
                $machineStatuses->each->delete();
            }

            $machineStatusLogs = MachineStatusLog::where('machine_id', $machineMaster->id)->get();
            if ($machineStatusLogs->isNotEmpty()) {
                $machineStatusLogs->each->delete();
            }
        });
    }

    public function node() {
        return $this->belongsTo(NodeMaster::class, 'node_id', 'id');
    }

    public function machineMasterLogs() {
        return $this->hasMany(MachineMasterLog::class, 'machine_id', 'id');
    }

    public function machineStatuses() {
        return $this->hasMany(MachineStatus::class, 'machine_id', 'id');
    }

    public function machineStatusLogs() {
        return $this->hasMany(MachineStatusLog::class, 'machine_id', 'id');
    }
}
