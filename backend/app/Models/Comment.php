<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'post_id',
        'name',
        'email',
        'body',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
