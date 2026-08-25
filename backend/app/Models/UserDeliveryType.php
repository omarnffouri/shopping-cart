<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDeliveryType extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'delivery_type_id',
        'time_slot_id',
        'delivery_date',
        "cart_id",
        "userable_id",
        "userable_type",
        'delivery_time',
        'delivery_price'
    ];

    public function userable(): \Illuminate\Database\Eloquent\Relations\MorphTo {
        return $this->morphTo();
    }

    public function deliveryType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DeliveryType::class);
    }

    public function timeSlot(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function cart(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(Cart::class);
    }
}
