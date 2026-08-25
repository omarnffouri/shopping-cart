<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'type', 'status', 'usage_limit', 'limit_per_customer', 'price', 'capped_price',
        'min_spend', 'code', 'start_time', 'end_time', 'admin_id', 'show_validity_date'
    ];

    protected $hidden = [
        'admin_id'
    ];

    protected $casts = [
        'show_validity_date' => 'boolean'
    ];
}
