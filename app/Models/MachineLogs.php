<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineLogs extends Model
{
    use HasFactory;

    protected $table = "machine_logs";

    protected $fillable = [
        'user_id',
        'device_id',
        'node_id',
        'machine_id',
        'machine_datetime',
        'device_datetime',
        'current_datetime',
        'mode',
        'speed',
        'pick',
        'status',
        'created_by',
        'updated_by',
    ];

    public $timestamps = false;

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function device() {
        return $this->belongsTo(Device::class, 'device_id', 'id');
    }

    public function node() {
        return $this->belongsTo(NodeMaster::class, 'node_id', 'id');
    }

    public function machine() {
        return $this->belongsTo(MachineMaster::class, 'machine_id', 'id');
    }
}
