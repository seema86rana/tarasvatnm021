<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NodeMaster extends Model
{
    use HasFactory;

    protected $table = "node_master";

    protected $fillable = [
        'name',
        'user_id',
        'device_id',
        'no_of_nodes',
        'status',
        'created_by',
        'updated_by',
    ];
}
