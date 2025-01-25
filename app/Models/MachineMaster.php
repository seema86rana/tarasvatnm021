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
}
