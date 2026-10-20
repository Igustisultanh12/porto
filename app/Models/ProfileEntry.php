<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileEntry extends Model
{
    use HasFactory;

    protected $fillable = ['kind', 'title', 'subtitle', 'body', 'url', 'sort_order'];
}
