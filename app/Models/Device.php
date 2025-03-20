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

    public static function boot()
    {
        parent::boot();

        static::deleting(function (Device $device) {
            $nodeMasters = NodeMaster::where('device_id', $device->id)->get();
            if ($nodeMasters->isNotEmpty()) {
                $nodeMasters->each->delete();
            }
        });
    }

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
