<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['moneybird_id', 'name', 'account_type', 'account_id', 'active', 'taxonomy_item'];

    protected $casts = [
        'active' => 'boolean',
        'taxonomy_item' => 'array',
    ];
}
