<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'permission',
        'status',
        'created_by',
        'updated_by',
    ];

    public $timestamps = false;

    public function users() {
        return $this->hasMany(User::class, 'id', 'role_id');
    }

    public function createdBy() {
        return $this->hasOne(User::class, 'id', 'created_by'); 
    }
}
