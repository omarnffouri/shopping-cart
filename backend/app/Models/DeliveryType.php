<?php

namespace App\Models;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryType extends Model
{
    use HasFactory , Translatable;

    protected $fillable = [
        'type_name',
        'type_code',
        'price',
        'currency',
        'country',
        'state',
        'description',
        'min_hours_advance',
        'available_for_today',
        'cutoff_time',
        'display_order',
        'is_active',
        'is_default'
    ];

    protected array $translatable = ['type_name'];


    public function timeSlots(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TimeSlot::class);
    }

    public function userDeliveryTypes(): \Illuminate\Database\Eloquent\Relations\HasMany{
        return $this->hasMany(UserDeliveryType::class);
    }
}
