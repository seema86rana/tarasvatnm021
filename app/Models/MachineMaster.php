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
        'status',
    ];

    public $timestamps = true;

    public static function boot()
    {
        parent::boot();

        static::deleting(function (MachineMaster $machineMaster) {
            $machineStatuses = MachineStatus::where('machine_id', $machineMaster->id)->get();
            if ($machineStatuses->isNotEmpty()) {
                $machineStatuses->each->delete();
            }

            $tempMachineStatuses = TempMachineStatus::where('machine_id', $machineMaster->id)->get();
            if ($tempMachineStatuses->isNotEmpty()) {
                $tempMachineStatuses->each->delete();
            }

            $machineLogs = MachineLog::where('machine_id', $machineMaster->id)->get();
            if ($machineLogs->isNotEmpty()) {
                $machineLogs->each->delete();
            }
        });
    }

    public function node()
    {
        return $this->belongsTo(NodeMaster::class, 'node_id', 'id');
    }

    public function machineLogs()
    {
        return $this->hasMany(MachineLog::class, 'machine_id', 'id');
    }

    public function machineStatuses()
    {
        return $this->hasMany(MachineStatus::class, 'machine_id', 'id');
    }

    public function tempMachineStatuses()
    {
        return $this->hasMany(TempMachineStatus::class, 'machine_id', 'id');
    }
}
