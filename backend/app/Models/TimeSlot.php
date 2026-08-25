<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_type_id',
        'slot_name',
        'start_time',
        'end_time',
        'available_if_before',
        'display_order',
        'is_active',
    ];

    public function deliveryType(): \Illuminate\Database\Eloquent\Relations\BelongsTo{
        return $this->belongsTo(DeliveryType::class, 'delivery_type_id', 'id');
    }

    public function userDeliveryTypes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserDeliveryType::class, 'time_slot_id', 'id');
    }
}
