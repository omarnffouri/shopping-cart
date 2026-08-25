@extends('layouts.email_layout')

@section('content')
    <div style="background: #fff3f3; border-left: 4px solid #dc3545; padding: 15px; margin-bottom: 20px;">
        <h3 style="color: #dc3545; margin-bottom: 10px;">{{__('lang.order_cancelled_title', [], $lang)}}</h3>
        <p>{{__('lang.order_cancelled_msg', ['order' => $order->order], $lang)}}</p>
    </div>

    @if($order->cancellation)
        <div style="background: #f8f9fa; border: 1px solid #ddd; padding: 15px; margin-bottom: 20px;">
            <h4 style="margin-bottom: 10px;">{{__('lang.cancellation_reason', [], $lang)}}:</h4>
{{--            <p style="margin-bottom: 10px;"><strong>{{ $order->cancellation->title ?? __('lang.order_cancelled', [], $lang) }}</strong></p>--}}
            <p>{{ $order->cancellation->message ?? __('lang.no_reason_provided', [], $lang) }}</p>

            @if($order->cancellation->refunded)
                <div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin-top: 15px; border-radius: 4px;">
                    <p style="color: #155724; margin: 0;">
                        <strong>{{__('lang.refund_status', [], $lang)}}:</strong> {{__('lang.refund_processed', [], $lang)}}
                    </p>
                </div>
            @endif
        </div>
    @endif

    <h3 class="mt-15">{{__('lang.cancelled_order', [], $lang)}} #{{ $order->order }}</h3>
    <p class="mb-20">{{__('lang.placed_on', [], $lang)}} {{ $order->created }}</p>

    <table class="mb-10">
        <tr>
            <th class="pb-10">{{__('lang.ship_to', [], $lang)}}</th>
            <th class="pb-10">{{__('lang.order_method', [], $lang)}}</th>
        </tr>

        <tr>
            <td style="width: 50%;">
                <div style="max-width: 300px;">
                    <h5 style="margin-bottom: 5px">{{ $order->address->name }}</h5>
                    <p>{{ $order->formatted_address }}</p>

                    @if($order->user)
                        <p>{{__('lang.email', [], $lang)}}: {{ $order->user->email }}</p>
                    @elseif($order->guest_user)
                        <p>{{__('lang.email', [], $lang)}}: {{ $order->guest_user->email }}</p>
                    @endif

                    <p>{{__('lang.phone', [], $lang)}}: {{ $order->address->phone }}</p>
                </div>
            </td>
            <td style="width: 50%;">{{ $order->order_method }}</td>
        </tr>
    </table><!--table-->

    <h4 class="mt-20 mb-10">{{__('lang.cancelled_items', [], $lang)}}</h4>

    <table style="background: #eee; border: 1px solid #ddd; border-bottom: none" class="mt-20 main-table border-tr">
        <tr>
            <th>{{__('lang.title', [], $lang)}}</th>
            <th>{{__('lang.delivery_fee', [], $lang)}}</th>
            <th>{{__('lang.quantity', [], $lang)}}</th>
            <th>{{__('lang.price', [], $lang)}}</th>
            <th>{{__('lang.total', [], $lang)}}</th>
        </tr>

        @foreach ($order->ordered_products as $op)
            <tr style="background: #fff">
                <td>
                    <b>{{ $op->product->title }}</b>
                    <span class="mt-5 f-9 block">{{ \App\Models\Helper\MailHelper::generatingAttribute($op) }}</span>
                </td>
                <td>
                    {{ $setting->currency_icon }}
                    {{ \App\Models\Helper\MailHelper::shippingPrice($op->shipping_place, $op->shipping_type) }}
                </td>
                <td>{{ $op->quantity }}</td>
                <td>{{ $setting->currency_icon }}{{ $op->selling }}</td>
                <td>{{ $setting->currency_icon }}{{ $op->selling * $op->quantity }}</td>
            </tr>
        @endforeach
    </table><!--table-->

    <table class="border-tr td-right-align footer-table"
           style="border: 1px solid #ddd; background: #eee;">
        <tr>
            <td style="width: 630px">{{__('lang.subtotal', [], $lang)}}</td>
            <td style="width: 70px;">{{ $setting->currency_icon }}{{ $order->calculated_price['subtotal'] }}</td>
        </tr>
        <tr>
            <td>{{__('lang.shipping_cost', [], $lang)}}</td>
            @if((float) $order->calculated_price['shipping_price'] > 0)
                <td>{{ $setting->currency_icon }}{{ $order->calculated_price['shipping_price'] }}</td>
            @else
                <td>{{__('lang.fre', [], $lang)}}</td>
            @endif
        </tr>

        @if ((int) $order->calculated_price['bundle_offer'] > 0)
            <tr>
                <td>{{__('lang.bundle_offer', [], $lang)}}</td>
                <td>{{ $setting->currency_icon }}{{ $order->calculated_price['bundle_offer'] }}</td>
            </tr>
        @endif

        @if ((int) $order->calculated_price['voucher_price'] > 0)
            <tr>
                <td>{{__('lang.voucher', [], $lang)}}</td>
                <td>{{ $setting->currency_icon }}{{ $order->calculated_price['voucher_price'] }}</td>
            </tr>
        @endif

        @if ((int) $order->calculated_price['tax'] > 0)
            <tr>
                <td>{{__('lang.tax', [], $lang)}}</td>
                <td>{{ $setting->currency_icon }}{{ $order->calculated_price['tax'] }}</td>
            </tr>
        @endif

        <tr style="background: #fff3f3;">
            <td><strong>{{__('lang.cancelled_amount', [], $lang)}}</strong></td>
            <td><strong>{{ $setting->currency_icon }}{{ $order->calculated_price['total_price'] }}</strong></td>
        </tr>

        @if($order->cancellation && $order->cancellation->refunded)
            <tr style="background: #d4edda;">
                <td><strong>{{__('lang.refund_amount', [], $lang)}}</strong></td>
                <td><strong>{{ $setting->currency_icon }}{{ $order->calculated_price['total_price'] }}</strong></td>
            </tr>
        @endif
    </table>

    @if($order->payment_done && !$order->cancellation->refunded)
        <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; margin-top: 20px; border-radius: 4px;">
            <p style="margin: 0;">
                <strong>{{__('lang.refund_notice', [], $lang)}}:</strong>
                {{__('lang.refund_processing_msg', [], $lang)}}
            </p>
        </div>
    @endif

    <p class="mt-20">{{__('lang.order_cancelled_footer', [], $lang)}}</p>

    <p class="mt-15">
        {{__('lang.view_order_history', [], $lang)}}
        <a href="{{ env('CLIENT_BASE_URL', config('env.url.CLIENT_BASE_URL')) }}/user/orders" class="btn order-btn-color" style="margin-left: 10px;">
            {{__('lang.view_orders', [], $lang)}}
        </a>
    </p>

{{--    <a href="{{ env('CLIENT_BASE_URL', config('env.url.CLIENT_BASE_URL')) }}/user/orders" .>{{__('lang.your_account', [], $lang)}}</a></p>--}}
@stop
