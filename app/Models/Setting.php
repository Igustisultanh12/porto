<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['data'];

    protected function casts(): array
    {
        return ['data' => 'array'];
    }

    public static function values(): array
    {
        return static::find(1)?->data ?? [];
    }
}
