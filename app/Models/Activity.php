<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'action', 'subject'];

    public static function record(string $action, string $subject): void
    {
        static::create(['user_id' => auth()->id(), 'action' => $action, 'subject' => $subject]);
    }
}
