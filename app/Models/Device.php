<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $table = 'devices';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'user_id',
        'shift',
        'status',
        'created_by',
        'updated_by',
    ];

    public $timestamps = true;

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id'); 
    }

    public function createdBy() {
        return $this->belongsTo(User::class, 'created_by', 'id'); 
    }

    public function nodes()
    {
        return $this->hasMany(NodeMaster::class, 'device_id', 'id');
    }
}
