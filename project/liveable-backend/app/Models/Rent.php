<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rent extends Model
{
    protected $fillable = [
        'checkin',
        'checkout',
        'has_pet',
        'guests_count',
        'user_id',
        'property_id',
        'details',
        'confirmed',
    ];

    // Relacionamentos — adicione estes dois métodos:
    public function payment()
    {
        return $this->hasOne(\App\Models\Payment::class);
    }

    public function property()
    {
        return $this->belongsTo(\App\Models\Property::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    protected $casts = [
        'checkin' => 'date',
        'checkout' => 'date',
        'has_pet' => 'boolean',
    ];
}
