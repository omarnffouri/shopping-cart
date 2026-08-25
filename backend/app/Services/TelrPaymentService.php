<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\Payment;
use Exception;

class TelrPaymentService
{
    protected mixed $storeId;
    protected mixed $authKey;
    protected bool $sandbox;
    protected string $baseUrl;

    public function __construct()
    {
        $payment = Payment::first();
        $this->storeId = $payment->telr_store_id;
        $this->authKey = $payment->telr_auth_key;
        $this->sandbox = $payment->telr_mode === 'sandbox';
        $this->baseUrl = 'https://secure.telr.com/gateway';
    }

    /**
     * Create a Telr payment session
     */
    public function createPaymentSession(Order $order): array
    {
        try {
            $order->loadMissing(['user_info', 'guest_user', 'address']);
            $name  = $order->user_info->name  ?? $order->guest_user->name  ?? '';
            $email = $order->user_info->email ?? $order->guest_user->email ?? '';
            $phone = $order->address->phone   ?? $order->guest_user->phone ?? ''; // adjust field names

            // Address mapping (adjust these keys to match your UserAddress columns)
            $addr1   = $order->address->address_line_1 ?? $order->address->address ?? '';
            $addr2   = $order->address->address_line_2 ?? '';
            $city    = $order->address->city ?? '';
            $region  = $order->address->state ?? $order->address->region ?? '';
            $zip     = $order->address->zip ?? $order->address->postcode ?? '';
            $country = $order->address->country ?? 'AE';

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post("{$this->baseUrl}/order.json", [
                'method' => 'create',
                'store' => (int)$this->storeId,
                'authkey' => $this->authKey,
                'framed' => 3,
                'order' => [
                    'cartid' => (string)$order->id,
                    'test' => $this->sandbox ? 1 : 0,
                    'amount' => number_format((float)$order->total_amount, 2, '.', ''),
                    'currency' => $order->currency,
                    'description' => "Order #{$order->id}",
                ],
                'customer' => [
                    'name'  => [
                        "title" => '',
                        "forenames" => (string) $name,
                        "surname" => ' '
                    ],
                    'email' => (string) $email,
                    'phone' => (string) $phone,
                    'address' => [
                        'line1'   => (string) $addr1,
                        'line2'   => (string) $addr2,
                        'city'    => (string) $city,
                        'region'  => (string) $region,
                        'country' => (string) $country,
                        'zip'     => (string) $zip,
                    ],
                ],
                'return' => [
                    'authorised' => config('env.url.CLIENT_BASE_URL') . '/payment/telr',
                    'declined' => config('env.url.CLIENT_BASE_URL') . '/payment/telr',
                    'cancelled' => config('env.url.CLIENT_BASE_URL') . '/payment/telr',
                ],
            ]);

            $result = $response->json();

            if (!$response->successful() || isset($result['error'])) {
                return $this->fail($result['error']['message'] ?? 'Payment session error.', $result);
            }

            $orderRef = $result['order']['ref'] ?? null;
            $payUrl = $result['order']['url'] ?? null;

            if ($orderRef) {
                $order->update(['payment_token' => $orderRef]);
            }

            return $this->ok('Payment session created', [
                'order_ref' => $orderRef,
                'payment_url' => $payUrl,
                'telr_order' => $result['order'] ?? [],
            ], $result);

        } catch (Exception $e) {
            Log::error("Telr Session Creation Failed: " . $e->getMessage());
            return $this->fail($e->getMessage());
        }
    }


    /**
     * Check payment status for a given order reference
     * @throws Exception
     */
    public function checkPaymentStatus(string $orderRef, string $payId = null): array
    {
        try {
            $response = Http::post("{$this->baseUrl}/order.json", [
                'method' => 'check',
                'store' => (int)$this->storeId,
                'authkey' => $this->authKey,
                'order' => ['ref' => $orderRef],
            ]);

            $result = $response->json();

            if (!$response->successful() || isset($result['error'])) {
                return $this->fail($result['error']['message'] ?? 'Telr status check failed.', $result);
            }

            $telrOrder = $result['order'] ?? [];
            $statusCode = $telrOrder['status']['code'] ?? null;
            $statusText = $telrOrder['status']['text'] ?? null;

            $orderId = $telrOrder['cartid'] ?? null;

            // Telr transaction reference (preferred)
            $tranRef = $telrOrder['transaction']['ref'] ?? $payId;

            $order = $orderId ? Order::find($orderId) : null;
            if (!$order) {
                return $this->fail('Invalid order id.', $result);
            }

            return $this->ok('Payment status fetched', [
                'status_code' => $statusCode,
                'status_text' => $statusText,
                'transaction_ref' => $tranRef,
                'order' => $order,
                'telr_order' => $telrOrder,
            ], $result);
        } catch (Exception $e) {
            Log::error("Telr Status Check Failed: " . $e->getMessage());
            return $this->fail($e->getMessage());
        }
    }


    /**
     * @throws Exception
     */
    public function makeRefund(Order $order): array
    {
        try {
            $check = $this->checkPaymentStatus($order->payment_token);

            if (!$check['success']) {
                return $this->fail('Cannot refund: status check failed.', $check['raw'] ?? []);
            }

            $telrOrder = $check['data']['telr_order'] ?? [];
            $statusCode = (int)($telrOrder['status']['code'] ?? 0);
            $statusText = $telrOrder['status']['text'] ?? null;

            if ($statusCode !== 3) {
                return $this->fail("Order not paid (Telr status: {$statusText}) – refund not allowed", $check['raw'] ?? []);
            }

            $tranRef = $order->trans_id ?: ($check['data']['transaction_ref'] ?? null);
            if (empty($tranRef)) {
                return $this->fail("Missing transaction ref – cannot refund");
            }

            $response = Http::asJson()->post("{$this->baseUrl}/remote.json", [
                'store' => (int)$this->storeId,
                'key' => $this->authKey,
                'tran' => [
                    'type' => 'refund',
                    'class' => 'ecom',
                    'cartid' => (string)$order->id,
                    'amount' => number_format((float)$order->total_amount, 2, '.', ''),
                    'currency' => $order->currency,
                    'ref' => $tranRef,
                    'description' => 'Order cancelled refund.',
                ],
            ]);

            $json = $response->json();

            if (!$response->successful()) {
                return $this->fail('Telr refund HTTP failed: ' . $response->status(), $json);
            }

            if (isset($json['error'])) {
                return $this->fail(
                    $json['error']['message'] ?? 'Telr refund error.',
                    $json,
                    $json['error']['code'] ?? null
                );
            }

            $tranStatus = $json['tran']['status'] ?? $json['transaction']['status'] ?? null;
            $msg = $json['tran']['message'] ?? $json['message'] ?? 'Refund response received.';

            if ($tranStatus === 'H') {
                return $this->ok('Refund pending', [
                    'tran_status' => $tranStatus,
                    'telr_ref' => $json['tran']['ref'] ?? null,
                ], $json);
            }

            $success = ($tranStatus === 'A');

            return $success
                ? $this->ok('Refund successful', [
                    'tran_status' => $tranStatus,
                    'telr_ref' => $json['tran']['ref'] ?? null,
                ], $json)
                : $this->fail("Refund not approved (status: {$tranStatus}) - {$msg}", $json);

        } catch (Exception $e) {
            Log::error("Telr refund request failed. " . $e->getMessage());
            return $this->fail($e->getMessage());
        }
    }

    private function ok(string $message, array $data = [], array $raw = []): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'raw' => $raw,
        ];
    }

    private function fail(string $message, array $raw = [], ?string $code = null): array
    {
        return [
            'success' => false,
            'message' => $message,
            'code' => $code,
            'data' => [],
            'raw' => $raw,
        ];
    }

}
