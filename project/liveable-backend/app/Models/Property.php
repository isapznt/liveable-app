<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'local',
        'type',
        'beds_qtd',
        'toilette',
        'rooms',
        'area',
        'owner_contact',
        'property_title',
        'wifi',
        'tv',
        'cooler',
        'air_conditioning',
        'washer',
        'microwave',
        'smoker',
        'pricePerDay',
        'status',
        'contract',
        'property_image_id',
        'is_featured',
    ];

    protected $casts = [
        'wifi' => 'boolean',
        'tv' => 'boolean',
        'cooler' => 'boolean',
        'air_conditioning' => 'boolean',
        'washer' => 'boolean',
        'microwave' => 'boolean',
        'is_featured'      => 'boolean',
    ];

    public function likes(): HasMany
    {
        return $this->hasMany(PropertyLike::class);
    }
    public function isLikedBy($user): bool
    {
        if (!$user) return false;

        $userId = $user instanceof User ? $user->id : $user;
        return $this->likes()->where('user_id', $userId)->exists();
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function isRent(Property $property): bool
    {
        return (bool) $property->status == 'rent';
    }
    public function isEnabled(Property $property): bool
    {
        return (bool) $property->status == 'enabled';
    }
    public function images()
    {
        return $this->hasMany(PropertyImage::class);
    }

    public function rents(): HasMany
    {
        return $this->hasMany(Rent::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
