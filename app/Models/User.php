<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\User as Authenticatable; 
use Illuminate\Notifications\Notifiable;

//class User extends Model
final class User extends Authenticatable implements AuthenticatableContract
{
    use HasFactory, Notifiable;
    // use Notifiable;
    protected $fillable = [
        'is_new',
        'facebook_id',
        'provider',
        'provider_id',
        'username',
        'fullname',
        'name',
        'email',
        'password',
        'phone',
        'address',
        'role',
        'reset_code',
        'reset_expires_at',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'reset_code',
    ];

    protected $casts = [
        'is_new' => 'boolean',
        'reset_expires_at' => 'datetime',
    ];

    // Quan hệ
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
