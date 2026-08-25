<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;


    protected $casts = [
        'payment_done' => 'integer',
        'viewed' => 'integer',
        'cancelled' => 'integer',
        'refund_response'=> 'array',
    ];


    protected $fillable = [
        'status', 'order_method', 'user_id', 'voucher_id', 'payment_done', 'order',
        'payment_token', 'currency', 'total_amount', 'user_address_id', 'user_token', 'viewed',
        'trans_id', 'pos_order_id', 'user_delivery_type_id','cancelled',
        'refund_status', 'refund_ref', 'refund_response'
    ];

    public const ORDER_STATUSES = [
        1 => "Pending",
        2 => "Confirmed",
        3 => "Picked up",
        4 => "On the Way",
        5 => 'Delivered',
        6 => 'Cancelled'
    ];


    public function pos_order(): HasOne
    {
        return $this->hasOne(PosOrder::class, 'id', 'pos_order_id');
    }

    public function address() : HasOne
    {
        return $this->hasOne(UserAddress::class, 'id', 'user_address_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }


    public function guest_user(): HasOne
    {
        return $this->hasOne(GuestUser::class, 'user_token', 'user_token');
    }


    public function user_info(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id')
            ->select(['id', 'name', 'email']);
    }

    public function voucher(): HasOne
    {
        return $this->hasOne(Voucher::class, 'id', 'voucher_id');
    }

    public function ordered_p(): HasMany
    {
        return $this->hasMany(OrderedProduct::class, 'order_id', 'id');
    }


    public function ordered_products(): HasMany
    {
        return $this->hasMany(OrderedProduct::class, 'order_id', 'id')
            ->select(['id', 'product_id', 'inventory_id', 'quantity', 'shipping_place_id', 'shipping_type',
                'selling', 'shipping_price', 'tax_price', 'bundle_offer', 'order_id']);
    }

    public function cancellation(): HasOne
    {
        return $this->hasOne(Cancellation::class, 'order_id', 'id');
    }

    public function ordered_price(): HasOne
    {
        return $this->hasOne(OrderedProduct::class)
            ->selectRaw('ordered_products.order_id, SUM(ordered_products.selling) as total')
            ->groupBy('ordered_products.order_id');
    }

    public function user_delivery_type(): BelongsTo
    {
        return $this->belongsTo(UserDeliveryType::class);
    }
}
