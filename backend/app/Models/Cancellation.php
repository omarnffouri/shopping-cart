<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cancellation extends Model
{

    protected $casts = [
        'refunded' => 'integer'
    ];


    protected $fillable = [
        'order_id', 'admin_id', 'title', 'message', 'refunded', 'user_token'
    ];

    use HasFactory;


    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    // Order relationship
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

}
