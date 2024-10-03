<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineMaster extends Model
{
    use HasFactory;

    protected $table = "machine_master";

    protected $fillable = [
        'user_id',
        'device_id',
        'node_id',
        'machine_name',
        'machine_display_name',
        'device_datetime',
        'status',
        'created_by',
        'updated_by',
    ];

    public $timestamps = false;
}
