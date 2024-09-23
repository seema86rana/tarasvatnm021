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
        'current_datetime',
        'mode',
        'speed',
        'pick',
        'status',
        'created_by',
        'updated_by',
    ];
}
