<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    protected $fillable = [
        'post_id',
        'phone',
        'label',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
