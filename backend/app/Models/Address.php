<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'street',
        'suite',
        'city',
        'zipcode',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function geo(): HasOne
    {
        return $this->hasOne(Geo::class);
    }
}
