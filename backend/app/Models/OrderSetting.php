<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderSetting extends Model
{
    protected $fillable = [
        'admin_id',
        'auto_cancel_minutes',
    ];
}
