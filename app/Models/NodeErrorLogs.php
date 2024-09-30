<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NodeErrorLogs extends Model
{
    use HasFactory;

    protected $table = "node_error_logs";

    protected $fillable = [
        'user_id',
        'device_id',
        'node_id',
        'status',
        'created_by',
        'updated_by',
    ];

    public $timestamps = false;
}
