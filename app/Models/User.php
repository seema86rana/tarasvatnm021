<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmailContract
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'role_id',
        'phone_number',
        'email',
        'password',
        'profile_image',
        'company_name',
        'gst_number',
        'address',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public $timestamps = true;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function (User $user) {
            $devices = Device::where('user_id', $user->id)->get();
            if ($devices->isNotEmpty()) {
                $devices->each->delete();
            }
        });
    }

    public function role() {
        return $this->belongsTo(Role::class, 'role_id', 'id'); 
    }

    public function createdBy() {
        return $this->belongsTo(User::class, 'created_by', 'id'); 
    }

    public function device() {
        return $this->hasMany(Device::class, 'user_id', 'id'); 
    }
}
