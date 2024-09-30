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

    public $timestamps = false;

    public function user() {
        return $this->hasOne(User::class, 'id', 'user_id'); 
    }

    public function createdBy() {
        return $this->hasOne(User::class, 'id', 'created_by'); 
    }
}
