<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartProductNote extends Model
{
    use HasFactory;
    protected $table = 'cart_product_note';
    protected $guarded = [];
}
