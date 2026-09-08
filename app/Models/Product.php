<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'brand',
        'model',
        'description',
        'unit',
        'price',
        'warranty_period',
        'specs',
    ];
}
