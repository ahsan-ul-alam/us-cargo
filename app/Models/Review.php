<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['name', 'location', 'rating', 'body', 'photo', 'is_approved', 'sort_order'];

    protected $casts = ['is_approved' => 'boolean', 'rating' => 'integer'];
}
