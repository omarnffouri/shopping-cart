<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderedProductNote extends Model
{
    use HasFactory;
    protected $table = 'ordered_products_notes';
    protected $fillable = ['ordered_product_id', 'image', 'message'];

    public function ordered_product()
    {
        return $this->belongsTo(OrderedProduct::class);
    }
}
