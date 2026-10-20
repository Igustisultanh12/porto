<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    use HasFactory;

    protected $fillable = ['content_id', 'slot', 'path', 'name', 'mime', 'size'];

    protected $hidden = ['path'];

    protected $appends = ['url'];

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    public function getUrlAttribute(): string
    {
        return route('media.show', $this->id, false);
    }
}
