<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'nom',
        'prenom',
        'login',
        'role',
        'password',
        'refresh_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'refresh_token',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function client(): HasOne
    {
        return $this->hasOne(Client::class, 'user_id');
    }
}