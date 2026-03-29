<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'phone',
        'website',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function address(): HasOne
    {
        return $this->hasOne(Address::class);
    }

    public function company(): HasOne
    {
        return $this->hasOne(Company::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function albums(): HasMany
    {
        return $this->hasMany(Album::class);
    }

    public function todos(): HasMany
    {
        return $this->hasMany(Todo::class);
    }
}
