<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'NIK',
        'Name',
        'Division',
        'Team',
        'Job_Function_KPI',
        'status',
        'region',
        'email',
        'password',
        'role',
        'avatar', // ditambahkan untuk avatar
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Accessor untuk avatar
    public function getAvatarAttribute($value)
    {
        if (!$value) return null;
        return asset('storage/avatars/' . $value);
    }
}
