<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NodeMaster extends Model
{
    use HasFactory;

    protected $table = "node_master";

    protected $fillable = [
        'device_id',
        'name',
        'status',
    ];

    public $timestamps = true;

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id', 'id');
    }

    public function machines()
    {
        return $this->hasMany(MachineMaster::class, 'node_id', 'id');
    }
}
