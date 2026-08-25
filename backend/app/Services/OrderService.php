<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderService
{
    public static function getValidOrder(Request $request, $lang): Order|JsonResponse
    {
        $order = Order::with(['voucher', 'address'])
            ->where('id', $request->id)
            ->first();

        if (!$order) {
            return response()->json([
                'error' => __('lang.invalid_order', [], $lang)
            ]);
        }

        $user = $request->user('user');

        if ($user && $order->user_id != $user->id) {
            return response()->json([
                'error' => __('lang.invalid_user', [], $lang)
            ]);
        } elseif (!$user && $request->user_token && $order->user_token != $request->user_token) {
            return response()->json([
                'error' => __('lang.invalid_user', [], $lang)
            ]);
        } elseif (!$user && !$request->user_token) {
            return response()->json([
                'error' => __('lang.error_token', [], $lang)
            ]);
        }

        return $order;
    }
}
