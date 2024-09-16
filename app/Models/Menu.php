<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menus';

    protected $fillable = [
        'name',
        'parent_id',
        'route',
        'icon',
        'position',
        'status',
        'created_by',
        'updated_by',
    ];

    public function subMenu() {
        return $this->hasMany(Menu::class, 'parent_id', 'id');
    }

    public function createdBy() {
        return $this->hasOne(User::class, 'id', 'created_by'); 
    }
}
