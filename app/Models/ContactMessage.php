<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = ['submission_id', 'name', 'email', 'subject', 'message', 'read_at', 'archived_at', 'notified_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime', 'archived_at' => 'datetime', 'notified_at' => 'datetime'];
    }
}
