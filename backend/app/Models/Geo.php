<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Geo extends Model
{
    protected $fillable = [
        'address_id',
        'lat',
        'lng',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
        ];
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }
}
