<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use HasApiTokens;
    use Notifiable;

    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'profile_picture',
        'banner',
        'role',
        'phone',
        'bio',
        'twitter',
        'instagram',
        'facebook',
        'share_socials',
        'provider',
        'provider_id',
    ];

    protected function casts(): array
    {
        return [
            'is_admin'      => 'boolean',
            'share_socials' => 'boolean',
        ];
    }

    public function property()
    {
        return $this->hasMany(Property::class);
    }

    public function likes()
    {
        return $this->hasMany(\App\Models\PropertyLike::class);
    }

    // No Model User, sobrescreva:
    public function sendPasswordResetNotification($token)
    {
        $url = "http://localhost:5173/reset-password?token={$token}&email={$this->email}";
        $userName = $this->name;
        $this->notify(new ResetPasswordNotification($url, $userName));
    }
}
