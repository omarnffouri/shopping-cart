<?php

namespace App\Http\Controllers;

use App\Models\Cancellation;
use App\Models\Cart;
use App\Models\DeliveryType;
use App\Models\GuestUser;
use App\Models\Helper\ControllerHelper;
use App\Models\Helper\FileHelper;
use App\Models\Helper\MailHelper;
use App\Models\Helper\Response;
use App\Models\Helper\Utils;
use App\Models\Helper\Validation;
use App\Models\IyzicoPayment;
use App\Models\Licence;
use App\Models\Order;
use App\Models\OrderedProduct;
use App\Models\OrderedProductNote;
use App\Models\OrderSetting;
use App\Models\Payment;
use App\Models\Plugin;
use App\Models\RatingReview;
use App\Models\ReviewImage;
use App\Models\Setting;
use App\Models\TimeSlot;
use App\Models\UpdatedInventory;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserDeliveryType;
use App\Models\Voucher;
use App\Services\OrderService;
use App\Services\TelrPaymentService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Razorpay\Api\Api;
use Mail;
use App\Models\PayFast;
use Stripe\Charge;
use Stripe\Stripe;

class OrdersController extends ControllerHelper
{
    public function vendorAll(Request $request)
    {
        try {
            $lang = $request->header('language');

            if (!$this->isVendor) {
                return Utils::isDataOwner(null, null);
            }

            if ($can = Utils::userCan($this->user, 'order.view')) {
                return $can;
            }

            $adminId = $this->user->id;
            $query = Order::query();

            $query = $query->with('address');
            $query = $query->with('user_info');
            $query = $query->with('cancellation');
            $query = $query->with('address');
            $query = $query->with('user');
            $query = $query->with('guest_user');

            $query = $query->with('ordered_products.shipping_place');
            $query = $query->orderBy('orders.' . $request->orderby, $request->type);

            if ($request->start_time || $request->end_time) {

                $startTime = $endTime = null;
                if ($request->time_zone && $request->start_time) {
                    $startTime = $request->start_time;
                }

                if ($request->time_zone && $request->end_time) {
                    $endTime = $request->end_time;
                }

                if ($startTime && $endTime) {
                    if ($startTime > $endTime) {
                        $temptEnd = $endTime;
                        $endTime = $startTime;
                        $startTime = $temptEnd;
                    }

                    $startOfDay = Carbon::parse($startTime)->startOfDay();
                    $endOfDay = Carbon::parse($endTime)->endOfDay();

                } else if ($startTime) {
                    $startOfDay = Carbon::parse($startTime)->startOfDay();
                    $endOfDay = Carbon::parse($startTime)->endOfDay();

                } else if ($endTime) {

                    $startOfDay = Carbon::parse($endTime)->startOfDay();
                    $endOfDay = Carbon::parse($endTime)->endOfDay();
                }

                if ($request->time_zone) {
                    $startOfDay = Utils::convertTimeToUTCzone($startOfDay, $request->time_zone);
                    $endOfDay = Utils::convertTimeToUTCzone($endOfDay, $request->time_zone);
                }

                $query = $query->where('created_at', '>=', $startOfDay);
                $query = $query->where('created_at', '<=', $endOfDay);
            }

            if ($request->filter) {

                foreach (explode(',', $request->filter) as $i) {
                    if ($i == 'cancelled') {
                        $query = $query->orWhere('cancelled', 1)
                            ->whereHas('ordered_products.product', function ($query) use ($adminId) {
                                $query->where('admin_id', $adminId);
                            });
                    }
                    if ($i == 'paid') {
                        $query = $query->orWhere('payment_done', Config::get('constants.paymentStatus.COMPLETED'))
                            ->whereHas('ordered_products.product', function ($query) use ($adminId) {
                                $query->where('admin_id', $adminId);
                            });
                    }
                    if ($i == 'unpaid') {
                        $query = $query->orWhere('payment_done',  Config::get('constants.paymentStatus.PENDING'))
                            ->whereHas('ordered_products.product', function ($query) use ($adminId) {
                                $query->where('admin_id', $adminId);
                            });
                    }
                    if ($i == 'card_payment') {
                        $query = $query
                            ->orWhere('order_method', Config::get('constants.paymentMethod.RAZORPAY'))
                            ->orWhere('order_method', Config::get('constants.paymentMethod.STRIPE'))
                            ->orWhere('order_method', Config::get('constants.paymentMethod.FLUTTERWAVE'))
                            ->orWhere('order_method', Config::get('constants.paymentMethod.IYZICO_PAYMENT'))
                            ->whereHas('ordered_products.product', function ($query) use ($adminId) {
                                $query->where('admin_id', $adminId);
                            });
                    }
                    if ($i == 'paypal') {
                        $query = $query
                            ->orWhere('order_method', Config::get('constants.paymentMethod.PAYPAL'))
                            ->whereHas('ordered_products.product', function ($query) use ($adminId) {
                                $query->where('admin_id', $adminId);
                            });
                    }
                    if ($i == 'cash_on_delivery') {
                        $query = $query->orWhere(
                            'order_method',
                            Config::get('constants.paymentMethod.CASH_ON_DELIVERY')
                        )
                            ->whereHas('ordered_products.product', function ($query) use ($adminId) {
                                $query->where('admin_id', $adminId);
                            });
                    }
                }
            } else {
                $query = $query->whereHas('ordered_products.product', function ($query) use ($adminId) {
                    $query->where('admin_id', $adminId);
                });
            }

            if ($lang) {
                $query = $query->with([
                    'ordered_products.product' => function ($query) use ($lang, $adminId) {
                        $query->where('products.admin_id', $adminId)
                            ->leftJoin(
                                'product_langs as pl',
                                function ($join) use ($lang) {
                                    $join->on('products.id', '=', 'pl.product_id');

                                    $join->where('pl.lang', $lang);
                                }
                            )
                            ->where('products.admin_id', $adminId)
                            ->select(
                                'products.id',
                                'products.title',
                                'products.image',
                                'products.selling',
                                'products.offered',
                                'products.shipping_rule_id',
                                'products.bundle_deal_id',
                                'products.unit',
                                'pl.title'
                            );
                    }
                ]);
                $query = $query->with([
                    'voucher' => function ($query) use ($lang) {
                        $query->leftJoin(
                            'voucher_langs as vl',
                            function ($join) use ($lang) {
                                $join->on('vouchers.id', '=', 'vl.voucher_id');
                                $join->where('vl.lang', $lang);
                            }
                        )
                            ->select('vouchers.*', 'vl.title');
                    }
                ]);
                $query = $query->with([
                    'ordered_products.updated_inventory.inventory_attributes.attribute_value' =>
                        function ($query) use ($lang) {
                            $query->leftJoin(
                                'attribute_value_langs as avl',
                                function ($join) use ($lang) {
                                    $join->on('attribute_values.id', '=', 'avl.attribute_value_id');
                                    $join->where('avl.lang', $lang);
                                }
                            )
                                ->with([
                                    'attribute' => function ($query) use ($lang) {

                                        $query->leftJoin(
                                            'attribute_langs as al',
                                            function ($join) use ($lang) {
                                                $join->on('attributes.id', '=', 'al.attribute_id');
                                                $join->where('al.lang', $lang);
                                            }
                                        )
                                            ->select('attributes.id', 'attributes.title', 'al.title');
                                    }
                                ])
                                ->select('attribute_values.*', 'avl.title');
                        }
                ]);
            } else {
                $query = $query->with([
                    'ordered_products.product' => function ($subQuery) use ($adminId) {
                        $subQuery->where('products.admin_id', $adminId)
                            ->select(
                                'products.id',
                                'products.title',
                                'products.image',
                                'products.selling',
                                'products.offered',
                                'products.shipping_rule_id',
                                'products.bundle_deal_id',
                                'products.unit',
                                'products.title'
                            );
                    }
                ]);
                $query = $query->with('voucher')
                    ->with('ordered_products.updated_inventory.inventory_attributes.attribute_value')
                    ->with('ordered_products.updated_inventory.inventory_attributes.attribute_value.attribute');
            }

            $query = $query->select('orders.*');
            $data = $query->paginate(Config::get('constants.api.PAGINATION'));

            $orderIds = [];

            if ($request->time_zone) {
                foreach ($data as $item) {
                    $orderIds[] = $item->id;

                    $orderedProducts = [];
                    foreach ($item->ordered_products as $j) {
                        if ($j->product) {
                            $orderedProducts[] = $j;
                        }
                    }

                    $item['calculated'] = Utils::calcPrice($item);

                    unset($item['ordered_products']);
                    $item['ordered_products'] = $orderedProducts;
                    $item['created'] = Utils::formatDate(Utils::convertTimeToUSERzone($item->created_at, $request->time_zone));
                }
            } else {
                foreach ($data as $item) {
                    $orderIds[] = $item->id;

                    $orderedProducts = [];
                    foreach ($item->ordered_products as $j) {
                        if ($j->product) {
                            $orderedProducts[] = $j;
                        }
                    }

                    $item['calculated'] = Utils::calcPrice($item);

                    unset($item['ordered_products']);
                    $item['ordered_products'] = $orderedProducts;
                    $item['created'] = Utils::formatDate($item->created_at);
                }
            }

            Order::whereIn('id', $orderIds)->where('viewed', false)->update([
                'viewed' => true
            ]);

            return response()->json(new Response($request->token, $data));

        } catch (Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }

    public function all(Request $request)
    {
        try {
            $lang = $request->header('language');

            if ($this->isVendor) {
                return Utils::isDataOwner(null, null);
            }

            if ($can = Utils::userCan($this->user, 'order.view')) {
                return $can;
            }
            $posPlugin = Plugin::where('name', 'pos')->first();

            $posLicenceValid = false;

            $baseURL = $request->url('/');
            $parse = parse_url($baseURL);
            $domain = $parse['host'];

            $isLocalhost = strpos($domain, "localhost") !== false || strpos($domain, "127.0.0.1") !== false;

            if ($isLocalhost) {
                $posLicenceValid = true;
            } else if ($posPlugin) {
                $validLicence = Utils::decryptLicence(
                    $posPlugin->secret_key,
                    $posPlugin->encrypt_key,
                    $posPlugin->encrypt_iv
                );

                if ($validLicence && $validLicence->d === $domain) {
                    $posLicenceValid = true;
                }
            }

            $query = Order::query();

            $query = $query->with('address');
            $query = $query->with('user_info');
            $query = $query->orderBy('orders.' . $request->orderby, $request->type);


            if ($posLicenceValid && $posPlugin && $posPlugin->active) {
                if (!$request->order_type || ($request->order_type && $request->order_type == 'website')) {
                    $query = $query->where('pos_order_id', null);
                } else if ($request->order_type && $request->order_type == 'pos') {
                    $query = $query->where('pos_order_id', '!=', null);
                }
            }

            if ($request->start_time || $request->end_time) {

                $startTime = $endTime = null;
                if ($request->time_zone && $request->start_time) {
                    $startTime = $request->start_time;
                }

                if ($request->time_zone && $request->end_time) {
                    $endTime = $request->end_time;
                }

                if ($startTime && $endTime) {
                    if ($startTime > $endTime) {
                        $temptEnd = $endTime;
                        $endTime = $startTime;
                        $startTime = $temptEnd;
                    }

                    $startOfDay = Carbon::parse($startTime)->startOfDay();
                    $endOfDay = Carbon::parse($endTime)->endOfDay();

                } else if ($startTime) {
                    $startOfDay = Carbon::parse($startTime)->startOfDay();
                    $endOfDay = Carbon::parse($startTime)->endOfDay();
                } else if ($endTime) {
                    $startOfDay = Carbon::parse($endTime)->startOfDay();
                    $endOfDay = Carbon::parse($endTime)->endOfDay();
                }

                if ($request->time_zone) {
                    $startOfDay = Utils::convertTimeToUTCzone($startOfDay, $request->time_zone);
                    $endOfDay = Utils::convertTimeToUTCzone($endOfDay, $request->time_zone);
                }

                $query = $query->where('created_at', '>=', $startOfDay);
                $query = $query->where('created_at', '<=', $endOfDay);
            }

            if ($request->filter) {
                $query = $query->where(function ($query) use ($request) {
                    foreach (explode(',', $request->filter) as $i) {
                        if ($i == 'cancelled') {
                            $query = $query->orWhere('cancelled', Config::get('constants.paymentStatus.COMPLETED'));
                        }
                        if ($i == 'paid') {
                            $query = $query->orWhere('payment_done', Config::get('constants.paymentStatus.COMPLETED'));
                        }
                        if ($i == 'unpaid') {
                            $query = $query->orWhere('payment_done',  Config::get('constants.paymentStatus.PENDING'));
                        }
                        if ($i == 'card_payment') {
                            $query = $query
                                ->orWhere('order_method', Config::get('constants.paymentMethod.RAZORPAY'))
                                ->orWhere('order_method', Config::get('constants.paymentMethod.STRIPE'))
                                ->orWhere('order_method', Config::get('constants.paymentMethod.FLUTTERWAVE'))
                                ->orWhere('order_method', Config::get('constants.paymentMethod.IYZICO_PAYMENT'));
                        }
                        if ($i == 'paypal') {
                            $query = $query
                                ->orWhere('order_method', Config::get('constants.paymentMethod.PAYPAL'));
                        }
                        if ($i == 'cash_on_delivery') {
                            $query = $query->orWhere(
                                'order_method',
                                Config::get('constants.paymentMethod.CASH_ON_DELIVERY')
                            );
                        }
                    }
                });
            }

            $query = $query->with('cancellation');
            $query = $query->with('address');
            $query = $query->with('user');
            $query = $query->with('guest_user');
            $query = $query->with('ordered_products.shipping_place');


            if ($lang) {
                $query = $query->with([
                    'ordered_products.product' => function ($query) use ($lang) {
                        $query->leftJoin(
                            'product_langs as pl',
                            function ($join) use ($lang) {
                                $join->on('products.id', '=', 'pl.product_id');
                                $join->where('pl.lang', $lang);
                            }
                        )
                            ->select(
                                'products.id',
                                'products.title',
                                'products.image',
                                'products.selling',
                                'products.offered',
                                'products.shipping_rule_id',
                                'products.bundle_deal_id',
                                'products.unit',
                                'pl.title'
                            );
                    }
                ]);
                $query = $query->with([
                    'voucher' => function ($query) use ($lang) {
                        $query->leftJoin(
                            'voucher_langs as vl',
                            function ($join) use ($lang) {
                                $join->on('vouchers.id', '=', 'vl.voucher_id');
                                $join->where('vl.lang', $lang);
                            }
                        )
                            ->select('vouchers.*', 'vl.title');
                    }
                ]);
                $query = $query->with([
                    'ordered_products.updated_inventory.inventory_attributes.attribute_value' =>
                        function ($query) use ($lang) {
                            $query->leftJoin(
                                'attribute_value_langs as avl',
                                function ($join) use ($lang) {
                                    $join->on('attribute_values.id', '=', 'avl.attribute_value_id');
                                    $join->where('avl.lang', $lang);
                                }
                            )
                                ->with([
                                    'attribute' => function ($query) use ($lang) {

                                        $query->leftJoin(
                                            'attribute_langs as al',
                                            function ($join) use ($lang) {
                                                $join->on('attributes.id', '=', 'al.attribute_id');
                                                $join->where('al.lang', $lang);
                                            }
                                        )
                                            ->select('attributes.id', 'attributes.title', 'al.title');
                                    }
                                ])
                                ->select('attribute_values.*', 'avl.title');
                        }
                ]);
            } else {

                $query = $query->with('ordered_products.product');
                $query = $query->with('voucher')
                    ->with('ordered_products.updated_inventory.inventory_attributes.attribute_value')
                    ->with('ordered_products.updated_inventory.inventory_attributes.attribute_value.attribute');
            }

            $query->where(function ($q) {
                $q->where('payment_done', Config::get('constants.paymentStatus.COMPLETED'))
                    ->orWhere(function ($sub) {
                        $sub->where('payment_done',  Config::get('constants.paymentStatus.PENDING'))
                            ->where('order_method', Config::get('constants.paymentMethod.CASH_ON_DELIVERY'));
                    });
            });

            $query = $query->select('orders.*');
            $data = $query->paginate(Config::get('constants.api.PAGINATION'));
            $orderIds = [];

            if ($request->time_zone) {
                foreach ($data as $item) {
                    $orderIds[] = $item->id;
                    $item['calculated'] = Utils::calcPrice($item);
                    $item['created'] = Utils::formatDate(Utils::convertTimeToUSERzone($item->created_at, $request->time_zone));
                }
            } else {
                foreach ($data as $item) {
                    $orderIds[] = $item->id;
                    $item['calculated'] = Utils::calcPrice($item);
                    $item['created'] = Utils::formatDate($item->created_at);
                }
            }

            Order::whereIn('id', $orderIds)->where('viewed', false)->update([
                'viewed' => true
            ]);


            return response()->json(new Response($request->token, $data));


        } catch (Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }

    public function abended(Request $request)
    {
        try {
            $lang = $request->header('language');

            if ($this->isVendor) {
                return Utils::isDataOwner(null, null);
            }

            if ($can = Utils::userCan($this->user, 'order.view')) {
                return $can;
            }
            $posPlugin = Plugin::where('name', 'pos')->first();

            $posLicenceValid = false;

            $baseURL = $request->url('/');
            $parse = parse_url($baseURL);
            $domain = $parse['host'];

            $isLocalhost = strpos($domain, "localhost") !== false || strpos($domain, "127.0.0.1") !== false;

            if ($isLocalhost) {
                $posLicenceValid = true;
            } else if ($posPlugin) {
                $validLicence = Utils::decryptLicence(
                    $posPlugin->secret_key,
                    $posPlugin->encrypt_key,
                    $posPlugin->encrypt_iv
                );

                if ($validLicence && $validLicence->d === $domain) {
                    $posLicenceValid = true;
                }
            }

            $query = Order::query();

            $query = $query->with('address');
            $query = $query->with('user_info');
            $query = $query->orderBy('orders.' . $request->orderby, $request->type);


            if ($posLicenceValid && $posPlugin && $posPlugin->active) {
                if (!$request->order_type || ($request->order_type && $request->order_type == 'website')) {
                    $query = $query->where('pos_order_id', null);
                } else if ($request->order_type && $request->order_type == 'pos') {
                    $query = $query->where('pos_order_id', '!=', null);
                }
            }

            if ($request->start_time || $request->end_time) {

                $startTime = $endTime = null;
                if ($request->time_zone && $request->start_time) {
                    $startTime = $request->start_time;
                }

                if ($request->time_zone && $request->end_time) {
                    $endTime = $request->end_time;
                }

                if ($startTime && $endTime) {
                    if ($startTime > $endTime) {
                        $temptEnd = $endTime;
                        $endTime = $startTime;
                        $startTime = $temptEnd;
                    }

                    $startOfDay = Carbon::parse($startTime)->startOfDay();
                    $endOfDay = Carbon::parse($endTime)->endOfDay();

                } else if ($startTime) {
                    $startOfDay = Carbon::parse($startTime)->startOfDay();
                    $endOfDay = Carbon::parse($startTime)->endOfDay();
                } else if ($endTime) {
                    $startOfDay = Carbon::parse($endTime)->startOfDay();
                    $endOfDay = Carbon::parse($endTime)->endOfDay();
                }

                if ($request->time_zone) {
                    $startOfDay = Utils::convertTimeToUTCzone($startOfDay, $request->time_zone);
                    $endOfDay = Utils::convertTimeToUTCzone($endOfDay, $request->time_zone);
                }

                $query = $query->where('created_at', '>=', $startOfDay);
                $query = $query->where('created_at', '<=', $endOfDay);
            }

            if ($request->filter) {
                $query = $query->where(function ($query) use ($request) {
                    foreach (explode(',', $request->filter) as $i) {
                        if ($i == 'cancelled') {
                            $query = $query->orWhere('cancelled', Config::get('constants.status.PUBLIC'));
                        }
                        if ($i == 'paid') {
                            $query = $query->orWhere('payment_done', Config::get('constants.paymentStatus.COMPLETED'));
                        }
                        if ($i == 'unpaid') {
                            $query = $query->orWhere('payment_done',  Config::get('constants.paymentStatus.PENDING'));
                        }
                        if ($i == 'card_payment') {
                            $query = $query
                                ->orWhere('order_method', Config::get('constants.paymentMethod.RAZORPAY'))
                                ->orWhere('order_method', Config::get('constants.paymentMethod.STRIPE'))
                                ->orWhere('order_method', Config::get('constants.paymentMethod.FLUTTERWAVE'))
                                ->orWhere('order_method', Config::get('constants.paymentMethod.IYZICO_PAYMENT'));
                        }
                        if ($i == 'paypal') {
                            $query = $query
                                ->orWhere('order_method', Config::get('constants.paymentMethod.PAYPAL'));
                        }
                        if ($i == 'cash_on_delivery') {
                            $query = $query->orWhere(
                                'order_method',
                                Config::get('constants.paymentMethod.CASH_ON_DELIVERY')
                            );
                        }
                    }
                });
            }

            $query = $query->with('cancellation');
            $query = $query->with('address');
            $query = $query->with('user');
            $query = $query->with('guest_user');
            $query = $query->with('ordered_products.shipping_place');


            if ($lang) {
                $query = $query->with([
                    'ordered_products.product' => function ($query) use ($lang) {
                        $query->leftJoin(
                            'product_langs as pl',
                            function ($join) use ($lang) {
                                $join->on('products.id', '=', 'pl.product_id');
                                $join->where('pl.lang', $lang);
                            }
                        )
                            ->select(
                                'products.id',
                                'products.title',
                                'products.image',
                                'products.selling',
                                'products.offered',
                                'products.shipping_rule_id',
                                'products.bundle_deal_id',
                                'products.unit',
                                'pl.title'
                            );
                    }
                ]);
                $query = $query->with([
                    'voucher' => function ($query) use ($lang) {
                        $query->leftJoin(
                            'voucher_langs as vl',
                            function ($join) use ($lang) {
                                $join->on('vouchers.id', '=', 'vl.voucher_id');
                                $join->where('vl.lang', $lang);
                            }
                        )
                            ->select('vouchers.*', 'vl.title');
                    }
                ]);
                $query = $query->with([
                    'ordered_products.updated_inventory.inventory_attributes.attribute_value' =>
                        function ($query) use ($lang) {
                            $query->leftJoin(
                                'attribute_value_langs as avl',
                                function ($join) use ($lang) {
                                    $join->on('attribute_values.id', '=', 'avl.attribute_value_id');
                                    $join->where('avl.lang', $lang);
                                }
                            )
                                ->with([
                                    'attribute' => function ($query) use ($lang) {

                                        $query->leftJoin(
                                            'attribute_langs as al',
                                            function ($join) use ($lang) {
                                                $join->on('attributes.id', '=', 'al.attribute_id');
                                                $join->where('al.lang', $lang);
                                            }
                                        )
                                            ->select('attributes.id', 'attributes.title', 'al.title');
                                    }
                                ])
                                ->select('attribute_values.*', 'avl.title');
                        }
                ]);
            } else {

                $query = $query->with('ordered_products.product');
                $query = $query->with('voucher')
                    ->with('ordered_products.updated_inventory.inventory_attributes.attribute_value')
                    ->with('ordered_products.updated_inventory.inventory_attributes.attribute_value.attribute');
            }

            $query->where('payment_done',  Config::get('constants.paymentStatus.PENDING'))
                ->whereNot('order_method', Config::get('constants.paymentMethod.CASH_ON_DELIVERY'));

            $query = $query->select('orders.*');
            $data = $query->paginate(Config::get('constants.api.PAGINATION'));
            $orderIds = [];

            if ($request->time_zone) {
                foreach ($data as $item) {
                    $orderIds[] = $item->id;
                    $item['calculated'] = Utils::calcPrice($item);
                    $item['created'] = Utils::formatDate(Utils::convertTimeToUSERzone($item->created_at, $request->time_zone));
                }
            } else {
                foreach ($data as $item) {
                    $orderIds[] = $item->id;
                    $item['calculated'] = Utils::calcPrice($item);
                    $item['created'] = Utils::formatDate($item->created_at);
                }
            }

            Order::whereIn('id', $orderIds)->where('viewed', false)->update([
                'viewed' => true
            ]);


            return response()->json(new Response($request->token, $data));


        } catch (Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }

    private function resolveAutoCancelMinutesForOrderId(int $orderId): int
    {
        // Default
        $defaultMinutes = 10;

        // Find admin_id from products inside the order (first one)
        $adminId = \DB::table('ordered_products as op')
            ->join('products as p', 'p.id', '=', 'op.product_id')
            ->where('op.order_id', $orderId)
            ->value('p.admin_id');

        // Admin-specific
        if ($adminId) {
            $adminRow = OrderSetting::where('admin_id', $adminId)->first();
            if ($adminRow && (int) $adminRow->auto_cancel_minutes > 0) {
                return (int) $adminRow->auto_cancel_minutes;
            }
        }

        // Global fallback (admin_id NULL)
        $globalRow = OrderSetting::first();
        if ($globalRow && (int) $globalRow->auto_cancel_minutes > 0) {
            return (int) $globalRow->auto_cancel_minutes;
        }

        return $defaultMinutes;
    }

    public function expiringSoon(Request $request)
    {
        try {
            $lang = $request->header('language');
            $timeZone = $request->time_zone;

            $userId = null;
            $userToken = null;

            if ($request->user('user')) {
                $userId = $request->user('user')->id;
            } else if ($request->user_token) {
                $userToken = $request->user_token;
            } else {
                return response()->json(Validation::errorLang($lang));
            }

            // First: find candidate orders (unpaid + pending + non-COD + not canceled)
            $baseQuery = Order::query();

            if ($userId) {
                $baseQuery->where('user_id', $userId);
            } else {
                $baseQuery->where('user_token', $userToken);
            }

            $baseQuery->where('payment_done', Config::get('constants.paymentStatus.PENDING'))
                ->where('status', Config::get('constants.orderStatus.PENDING'))
                ->where(function ($q) {
                    $q->whereNull('cancelled')
                        ->orWhere('cancelled', '!=', Config::get('constants.status.PUBLIC'));
                })
                ->whereNot('order_method', Config::get('constants.paymentMethod.CASH_ON_DELIVERY'));

            // Load the oldest pending unpaid order first (closest to expiry once minutes applied)
            $orders = (clone $baseQuery)->orderBy('created_at', 'DESC')->get();

            // Auto-cancel any expired ones (offline-safe), and pick the first non-expired
            $nowMs = Carbon::now()->getTimestampMs();
            $picked = null;
            foreach ($orders as $order) {
                $minutes = $this->resolveAutoCancelMinutesForOrderId((int) $order->id);
                $expiresAtMs = $order->created_at->copy()->addMinutes($minutes)->getTimestampMs();

//                if ($expiresAtMs <= $nowMs) {
//                    // expired => cancel as system "Payment failed!"
//                    if (!$order->cancelled) {
//                        $order->update(['cancelled' => true]);
//
//                        $cancellation = Cancellation::updateOrCreate(
//                            ['order_id' => $order->id, 'admin_id' => null],
//                            ['title' => 'System Canceled', 'message' => 'Payment failed!']
//                        );
//
//                        $order->setRelation('cancellation', $cancellation);
//                        $this->reStockInventory($order->ordered_p);
//
//                        ReviewImage::leftJoin('rating_reviews', 'review_images.rating_review_id', '=', 'rating_reviews.id')
//                            ->where('rating_reviews.order_id', $order->id)
//                            ->delete();
//
//                        RatingReview::where('order_id', $order->id)->delete();
//                    }
//                    continue;
//                }

                $picked = $order;
                $picked['show_retry_dot'] = true;
                $picked['auto_cancel_minutes'] = $minutes;
                $picked['expires_at_ms'] = $expiresAtMs;
                break;
            }

            if (!$picked) {
                return response()->json(new Response($request->token, null));
            }

            if ($timeZone) {
                $picked['created'] = Utils::formatDate(
                    Utils::convertTimeToUSERzone($picked['created_at'], $timeZone)
                );
            } else {
                $picked['created'] = Utils::formatDate($picked['created_at']);
            }

            return response()->json(new Response($request->token, $picked));
        } catch (Exception $ex) {
            report($ex);
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }

    public function find(Request $request, $id)
    {
        try {
            $lang = $request->header('language');

            if ($can = Utils::userCan($this->user, 'order.view')) {
                return $can;
            }

            $adminId = $this->user->id;

            $query = Order::query();
            $query = $query->with('cancellation');
            $query = $query->with('address');
            $query = $query->with('user');
            $query = $query->with('guest_user');

            $query = $query->with([
                'user_delivery_type' => function ($query) {
                    $query->select('id', 'delivery_type_id', 'delivery_time', 'delivery_price', 'delivery_date')
                        ->with('deliveryType');
                }
            ]);
            $query = $query->with('ordered_products.shipping_place');
            $query = $query->with('ordered_products.note');

            if ($lang) {
                if (!$this->isSuperAdmin) {
                    $query = $query->with([
                        'ordered_products.product' => function ($query) use ($lang, $adminId) {
                            $query->with([
                                'product_images' => function ($query) {
                                    $query->with([
                                        'attributes' => function ($query) {
                                        }
                                    ]);
                                }
                            ]);
                            $query->where('products.admin_id', $adminId)
                                ->leftJoin(
                                    'product_langs as pl',
                                    function ($join) use ($lang) {
                                        $join->on('products.id', '=', 'pl.product_id');
                                        $join->where('pl.lang', $lang);
                                    }
                                )
                                ->select(
                                    'products.id',
                                    'products.title',
                                    'products.image',
                                    'products.selling',
                                    'products.offered',
                                    'products.shipping_rule_id',
                                    'products.bundle_deal_id',
                                    'products.unit',
                                    'pl.title'
                                );
                        }
                    ]);
                } else {
                    $query = $query->with([
                        'ordered_products.product' => function ($query) use ($lang) {
                            $query->with([
                                'product_images' => function ($query) {
                                    $query->with([
                                        'attributes' => function ($query) {
                                        }
                                    ]);
                                }
                            ]);
                            $query->leftJoin(
                                'product_langs as pl',
                                function ($join) use ($lang) {
                                    $join->on('products.id', '=', 'pl.product_id');
                                    $join->where('pl.lang', $lang);
                                }
                            )
                                ->select(
                                    'products.id',
                                    'products.title',
                                    'products.image',
                                    'products.selling',
                                    'products.offered',
                                    'products.shipping_rule_id',
                                    'products.bundle_deal_id',
                                    'products.unit',
                                    'pl.title'
                                );
                        }
                    ]);
                }
                $query = $query->with([
                    'voucher' => function ($query) use ($lang) {
                        $query->leftJoin(
                            'voucher_langs as vl',
                            function ($join) use ($lang) {
                                $join->on('vouchers.id', '=', 'vl.voucher_id');
                                $join->where('vl.lang', $lang);
                            }
                        )
                            ->select('vouchers.*', 'vl.title');
                    }
                ]);
                $query = $query->with([
                    'ordered_products.updated_inventory.inventory_attributes.attribute_value' =>
                        function ($query) use ($lang) {
                            $query->leftJoin(
                                'attribute_value_langs as avl',
                                function ($join) use ($lang) {
                                    $join->on('attribute_values.id', '=', 'avl.attribute_value_id');
                                    $join->where('avl.lang', $lang);
                                }
                            )
                                ->with([
                                    'attribute' => function ($query) use ($lang) {
                                        $query->leftJoin(
                                            'attribute_langs as al',
                                            function ($join) use ($lang) {
                                                $join->on('attributes.id', '=', 'al.attribute_id');
                                                $join->where('al.lang', $lang);
                                            }
                                        )
                                            ->select('attributes.id', 'attributes.title', 'al.title');
                                    }
                                ])
                                ->select('attribute_values.*', 'avl.title');
                        }
                ]);
            } else {
                if (!$this->isSuperAdmin) {
                    $query = $query->with([
                        'ordered_products.product' => function ($query) use ($adminId) {
                            $query->with([
                                'product_images' => function ($query) {
                                    $query->with([
                                        'attributes' => function ($query) {
                                        }
                                    ]);
                                }
                            ]);
                            $query->where('products.admin_id', $adminId)
                                ->select(
                                    'products.id',
                                    'products.title',
                                    'products.image',
                                    'products.selling',
                                    'products.offered',
                                    'products.shipping_rule_id',
                                    'products.bundle_deal_id',
                                    'products.unit',
                                    'products.title'
                                );
                        }
                    ]);
                } else {
                    $query = $query->with([
                        'ordered_products.product' => function ($query) {
                            $query->with([
                                'product_images' => function ($query) {
                                    $query->with([
                                        'attributes' => function ($query) {
                                        }
                                    ]);
                                }
                            ]);
                        }
                    ]);
                }

                $query = $query->with('voucher')
                    ->with('ordered_products.updated_inventory.inventory_attributes.attribute_value.attribute');
            }

            if (!$this->isSuperAdmin) {
                $query = $query->whereHas('ordered_products.product', function ($query) use ($adminId) {
                    $query->where('admin_id', $adminId);
                });
            }

            $order = $query->find($id);

            if (is_null($order)) {
                return response()->json(Validation::nothingFoundLang($lang));
            }

            $order['calculated'] = Utils::calcPrice($order);

            $orderedProducts = [];
            foreach ($order->ordered_products as $j) {
                if ($j->product) {
                    $orderedProducts[] = $j;
                }
            }
            unset($order['ordered_products']);
            $order['ordered_products'] = $orderedProducts;

            if ($request->time_zone) {
                $order['created'] = Utils::formatDate(Utils::convertTimeToUSERzone($order->created_at, $request->time_zone));
            } else {
                $order['created'] = Utils::formatDate($order->created_at);
            }

            return response()->json(new Response($request->token, $order));

        } catch (Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }

    public function updatePaymentMethod(Request $request)
    {
        try {
            $lang = $request->header('language');

            if ($this->isVendor) {
                return Utils::isDataOwner(null, null);
            }

            if ($can = Utils::userCan($this->user, 'order.edit')) {
                return $can;
            }

            $validate = Validation::orderStatus($request);
            if ($validate) {
                return response()->json($validate);
            }

            $order = Order::find($request->id);

            if (is_null($order)) {
                return response()->json(Validation::nothingFoundLang($lang));
            }

            Order::where('id', $request->id)->update(['order_method' => $request->order_method]);

            return response()->json(new Response($request->token, [
                'order_method' => $request->order_method,
                'id' => $request->id
            ]));

        } catch (Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }

    public function updatePaymentStatus(Request $request)
    {
        try {
            $lang = $request->header('language');

            if ($this->isVendor) {
                return Utils::isDataOwner(null, null);
            }

            if ($can = Utils::userCan($this->user, 'order.edit')) {
                return $can;
            }

            $validate = Validation::orderStatus($request);
            if ($validate) {
                return response()->json($validate);
            }

            $order = Order::find($request->id);

            if (is_null($order)) {
                return response()->json(Validation::nothingFoundLang($lang));
            }

            Order::where('id', $request->id)->update(['payment_done' => $request->payment_done]);

            return response()->json(new Response($request->token, [
                'payment_done' => $request->payment_done,
                'id' => $request->id
            ]));

        } catch (Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }

    public function updateStatus(Request $request)
    {
        try {
            $lang = $request->header('language');

            if ($this->isVendor) {
                return Utils::isDataOwner(null, null);
            }

            if ($can = Utils::userCan($this->user, 'order.edit')) {
                return $can;
            }

            $validate = Validation::orderStatus($request);
            if ($validate) {
                return response()->json($validate);
            }

            $order = Order::with('cancellation')->find($request->id);

            if (is_null($order)) {
                return response()->json(Validation::nothingFoundLang($lang));
            }

            $updatedStatus['status'] = $request->status;

            if (
                (int) Config::get('constants.orderStatus.DELIVERED') == (int) $request->status &&
                (int) Config::get('constants.paymentMethod.CASH_ON_DELIVERY') == (int) $order->order_method
            ) {
                $updatedStatus['payment_done'] = Config::get('constants.paymentStatus.COMPLETED');
            }
            if ((int) Config::get('constants.orderStatus.CANCELLED') === (int) $request->status) {
                $updatedStatus['cancelled'] = Config::get('constants.status.PUBLIC'); // 1
            }

            Order::where('id', $request->id)->update($updatedStatus);

            if ((int) Config::get('constants.orderStatus.CANCELLED') == (int) $request->status) {
                $admin = $request->user('admin');

                // Create a cancellation record
                Cancellation::create([
                    'order_id' => $order->id,
                    'admin_id' => $admin ? $admin->id : null,
                    'title' => 'Cancelled by Admin',
                    'message' => $request->message ?? 'No reason provided',
                    'refunded' => $request->refundable ?? 0,
                ]);

                // Update order cancelled flag
                $order->update(['cancelled' => Config::get('constants.status.PUBLIC')]);
                $order->refresh();
                $order->load('cancellation');
                // Restock inventory
                $this->reStockInventory($order->ordered_p);

                // Delete existing rating/reviews if any
                ReviewImage::leftJoin('rating_reviews', 'review_images.rating_review_id', '=', 'rating_reviews.id')
                    ->where('rating_reviews.order_id', $order->id)->delete();
                RatingReview::where('order_id', $order->id)->delete();

                // Send Email
                MailHelper::sendingCancellationEmail($order->id, $lang);
            }

            return response()->json(new Response($request->token, [
                'result' =>
                    [
                        'status' => $request->status,
                        'payment_done' => $updatedStatus['payment_done'] ?? $order->payment_done,
                        'id' => $request->id,
                        'cancellation' => $order->cancellation,
                    ]
            ]));

        } catch (Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $lang = $request->header('language');

            if ($this->isVendor) {
                return Utils::isDataOwner(null, null);
            }

            $ids = explode(",", $id);

            foreach ($ids as $i) {

                $order = Order::find($i);

                if (is_null($order)) {
                    return response()->json(Validation::nothingFoundLang($lang));
                }

                OrderedProduct::where('order_id', $i)->delete();

                Cancellation::where('order_id', $i)->delete();

                Order::where('id', $i)->delete();
            }
            return response()->json(new Response($request->token, true));
            //return response()->json(Validation::errorTokenLang($request->token, $lang));

        } catch (Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }

    public function byUser(Request $request)
    {
        try {
            $lang = $request->header('language');
            $query = Order::query();
            $query = $query->with([
                'user_delivery_type' => function ($query) {
                    $query->select('id', 'delivery_type_id', 'delivery_time', 'delivery_price', 'delivery_date')
                        ->with('deliveryType');
                }
            ]);

            if ($request->order_id) {
                $query = $query->with('address');
                $query = $query->with('user_info');
                $query = $query->with('voucher');
                $query = $query->with('cancellation');
                if (!$lang) {
                    $query = $query->with([
                        'ordered_products' => function ($query) {
                            $query->with(['updated_inventory.inventory_attributes.attribute_value.attribute']);
                            $query->with(['shipping_place']);
                            $query->with(['product' => function ($query) {
                                $query->with(['product_images.attributes']);
                                $query->with(['bundle_deal']);
                            }]);
                            $query->with(['note:ordered_product_id,message,image']);
                        }
                    ]);
                }

                $order = $query->find($request->order_id);

                if (is_null($order)) {
                    return response()->json(Validation::error(
                        $request->token,
                        __('lang.no_order', [], $lang)
                    ));
                }

                if ($request->user('user')) {

                    if ((int) $order->user_id !== $request->user('user')->id) {
                        return response()->json(Validation::error(
                            $request->token,
                            __('lang.not_order', [], $lang),
                            'form',
                            null,
                            403
                        ));
                    }

                } else if ($request->user_token) {
                    if ($order->user_token !== $request->user_token) {
                        return response()->json(Validation::error(
                            $request->token,
                            __('lang.not_order', [], $lang),
                            'form',
                            null,
                            403
                        ));
                    }

                } else {
                    return response()->json(Validation::errorLang($lang));
                }


                $order['user'] = $order->user_info;
                unset($order->user_info);
                $order['calculated'] = Utils::calcPrice($order);
                $order['created'] = Utils::formatDate(Utils::convertTimeToUSERzone($order->created_at, $request->time_zone));

                return response()->json(new Response($request->token, $order));

            } else {
                if ($lang) {
                    $query = $query->with([
                        'ordered_products.product' => function ($query) use ($lang) {
                            $query->leftJoin('product_langs as pl', function ($join) use ($lang) {
                                $join->on('pl.product_id', '=', 'products.id');
                                $join->where('pl.lang', $lang);
                            })
                                ->select(
                                    'products.id',
                                    'pl.title',
                                    'products.slug',
                                    'products.image',
                                    'products.selling',
                                    'products.offered',
                                    'products.shipping_rule_id',
                                    'products.bundle_deal_id',
                                    'pl.unit'
                                );
                        }
                    ]);
                } else {
                    $query = $query->with('ordered_products.product');
                }

                if ($request->allow_note) {
                    $query = $query->with('ordered_products.note');
                }

                $query = $query->orderBy('created_at', 'DESC');

                if ($request->cancelled) {
                    $query = $query->where('cancelled', $request->cancelled);
                }

                if ($request->has('is_paid')) {
                    $isPaid = filter_var($request->is_paid, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

                    if ($isPaid) {
                        $query->where(function (Builder $query) {
                            $query->where('payment_done', Config::get('constants.paymentStatus.COMPLETED'))
                                ->orWhere('order_method', Config::get('constants.paymentMethod.CASH_ON_DELIVERY'));
                        });
                    } else {
                        $query->where('payment_done', Config::get('constants.paymentStatus.PENDING'))
                            ->where('order_method', '<>', Config::get('constants.paymentMethod.CASH_ON_DELIVERY'));
                    }
                    /*if ($request->unpaid){
                        $query->where(function (Builder $query) {
                            $query->where('payment_done', Config::get('constants.paymentStatus.COMPLETED'))
                                ->orWhere('order_method', Config::get('constants.paymentMethod.CASH_ON_DELIVERY'));
                        });
                    }*/
                }

                if ($request->card_payment) {
                    $query = $query->where(function ($query) {
                        $query->where('order_method', Config::get('constants.paymentMethod.RAZORPAY'))
                            ->orWhere('order_method', Config::get('constants.paymentMethod.STRIPE'))
                            ->orWhere('order_method', Config::get('constants.paymentMethod.TELR'));
                    });

                } else if ($request->cash_on_delivery) {
                    $query = $query->where('order_method', Config::get('constants.paymentMethod.CASH_ON_DELIVERY'));
                }

                if ($request->user('user')) {
                    $query = $query->where('user_id', $request->user('user')->id);
                } else if ($request->user_token) {
                    $query = $query->where('user_token', $request->user_token);
                } else {
                    return response()->json(Validation::errorLang($lang));
                }


                $data = $query->paginate(Config::get('constants.frontend.PAGINATION'));

                if ($request->time_zone) {
                    foreach ($data as $item) {
                        $item['created'] = Utils::formatDate(Utils::convertTimeToUSERzone($item->created_at, $request->time_zone));
                    }
                } else {
                    foreach ($data as $item) {
                        $item['created'] = Utils::formatDate($item->created_at);
                    }
                }
                return response()->json(new Response($request->token, $data));
            }

        } catch (Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }

    public function transaction(Request $request)
    {
        try {
            $lang = $request->header('language');

            $params = json_decode(Utils::jsDecryption($request->data));
            $request->request->add(['user_token' => $params->user_token]);
            $request->request->add(['id' => $params->id]);
            $request->request->add(['trans_id' => $params->trans_id]);

            if ($validate = Validation::transId($request)) {
                return response()->json($validate);
            }

            $order = OrderService::getValidOrder($request, $lang);

            if ($order instanceof JsonResponse) {
                return $order;
            }

            $result = Order::where('id', $request->id)->update([
                'trans_id' => $request->trans_id
            ]);
        } catch (Exception $e) {
            return response()->json(Validation::error($request->token, $e->getMessage()));
        }
        return response()->json(new Response($request->token, $result));
    }

    public function paymentDone(Request $request)
    {
        try {

            $lang = $request->header('language');
            $params = json_decode(Utils::jsDecryption($request->data));

            $request->request->add(['user_token' => $params->user_token]);
            $request->request->add(['id' => $params->id]);
            $request->request->add(['payment_token' => $params->payment_token]);
            $request->request->add(['order_method' => $params->order_method]);

            $validate = Validation::orderStatus($request);
            if ($validate) {
                return response()->json($validate);
            }

            $order = OrderService::getValidOrder($request, $lang);

            if ($order instanceof JsonResponse) {
                return $order;
            }

            $payment = Payment::first();

            if ((int) $payment->cash_on_delivery != 1 && ((int) $request->order_method == Config::get('constants.paymentMethod.CASH_ON_DELIVERY'))) {
                return response()->json(Validation::error(
                    $request->token,
                    __('lang.accepted_cod', [], $lang)
                ));
            }
            if ((int) $payment->paypal != 1 && (int) $request->order_method == Config::get('constants.paymentMethod.PAYPAL')) {
                return response()->json(Validation::error(
                    $request->token,
                    __('lang.accepted_paypal', [], $lang)
                ));
            }
            if ((int) $payment->stripe != 1 && (int) $request->order_method == Config::get('constants.paymentMethod.STRIPE')) {
                return response()->json(Validation::error(
                    $request->token,
                    __('lang.accepted_gateway', [], $lang)
                ));
            }
            if ((int) $payment->razorpay != 1 && (int) $request->order_method == Config::get('constants.paymentMethod.RAZORPAY')) {
                return response()->json(Validation::error(
                    $request->token,
                    __('lang.accepted_gateway', [], $lang)
                ));
            }
            if ((int) $payment->flutterwave != 1 && (int) $request->order_method == Config::get('constants.paymentMethod.FLUTTERWAVE')) {
                return response()->json(Validation::error(
                    $request->token,
                    __('lang.accepted_gateway', [], $lang)
                ));
            }
            if ((int) $payment->bank != 1 && (int) $request->order_method == Config::get('constants.paymentMethod.BANK')) {
                return response()->json(Validation::error(
                    $request->token,
                    __('lang.accepted_gateway', [], $lang)
                ));
            }

            $paymentDone = false;

            if ((int) $request->order_method == Config::get('constants.paymentMethod.FLUTTERWAVE')) {
                try {
                    $con = \Flutterwave\Helper\Config::setUp(
                        $payment->fw_secret_key,
                        $payment->fw_public_key,
                        $payment->fw_encryption_key,
                        $payment->fw_environment
                    );
                    $transactions = new \Flutterwave\Service\Transactions($con);
                    $response = $transactions->verify($request->payment_token);

                    if ($response->status === "success") {
                        $paymentDone = true;
                    } else {
                        return response()->json(Validation::error(
                            $request->token,
                            __('lang.flutterwave_error', [], $lang)
                        ));
                    }

                } catch (Exception $e) {
                    if (str_contains($e->getMessage(), 'The stream or file')) {
                        $paymentDone = true;
                    } else {
                        return response()->json(Validation::error(
                            $request->token,
                            $e->getMessage()
                        ));
                    }
                }
            }
            if ((int) $request->order_method == Config::get('constants.paymentMethod.PAYPAL')) {
                $paymentDone = true;
            }
            if ((int) $request->order_method == Config::get('constants.paymentMethod.RAZORPAY')) {
                if ($order->payment_token != $request->payment_token) {
                    return response()->json(Validation::error(
                        $request->token,
                        __('lang.invalid_token', [], $lang)
                    ));
                }
                $paymentDone = true;

            }
            if ((int) $request->order_method == Config::get('constants.paymentMethod.STRIPE')) {
                // Calculating price
                $orderedProduct = OrderedProduct::with('product.bundle_deal')
                    ->where('order_id', $request->id)
                    ->get();
                $voucherPrice = 0;
                $shippingPrice = 0;
                $subtotal = 0;
                foreach ($orderedProduct as $item) {
                    // Bundle calculation
                    $bundleQtyOffer = 0;
                    $bundleDeal = $item->product->bundle_deal;
                    if ($bundleDeal) {
                        if ($item->quantity >= $bundleDeal->buy) {
                            $bundleQtyOffer = $bundleDeal->free;
                        }
                    }
                    $shippingPrice += $item->shipping_price;

                    $subtotal += ($item->selling * ($item->quantity - $bundleQtyOffer))
                        + ($item->tax_price * (int) $item->quantity);
                }
                if ($order->voucher) {
                    if ((int) $order->voucher->type === (int) Config::get('constants.priceType.FLAT')) {
                        $voucherPrice = $order->voucher->price;
                    } else {
                        $voucherPrice = number_format((float) ($order->voucher->price * $subtotal) / 100, 2, '.', '');
                    }
                    if (!is_null($order->voucher->capped_price) && $voucherPrice > $order->voucher->capped_price) {
                        $voucherPrice = (int) $order->voucher->capped_price;
                    }
                }
                $totalPrice = $subtotal - $voucherPrice + $shippingPrice;

                $sSecret = $payment->stripe_secret;
                $setting = Setting::select('currency')->first();

                Stripe::setApiKey($sSecret);
                Charge::create([
                    'amount' => $totalPrice * 100,
                    'currency' => $setting->currency,
                    'source' => $request->payment_token,
                    'description' => 'order_id_' . $order->id,
                    'receipt_email' => $order->address->email,
                    'metadata' => ['order_id' => $order->id]
                ]);

                $paymentDone = true;
            }
            if ((int) $request->order_method == Config::get('constants.paymentMethod.IYZICO_PAYMENT')) {

                $result["iyzico_payment"] = IyzicoPayment::initIyzico($request, $order->id);

                return response()->json(new Response($request->token, $result));

            }
            if ((int) $request->order_method == Config::get('constants.paymentMethod.PAYFAST')) {
                $result["payfast"] = PayFast::getPayFastForm($payment, $order, [
                    'name' => $order->address->name,
                    'email' => $order->address->email
                ], $order->total_amount);

                return response()->json(new Response($request->token, $result));
            }

            $result = Order::where('id', $request->id)->update([
                'payment_done' => $paymentDone,
                'order_method' => $request->order_method
            ]);
        } catch (Exception $e) {
            return response()->json(Validation::error($request->token, $e->getMessage()));
        }
        return response()->json(new Response($request->token, $result));
    }

    public function action(Request $request)
    {
        try {
            $lang = $request->header('language');

            $params = json_decode(Utils::jsDecryption($request->data));

            $request->request->add(['user_token' => $params->user_token]);
            $request->request->add(['order_method' => $params->order_method]);
            $request->request->add(['voucher' => $params->voucher]);
            $request->request->add(['time_zone' => $params->time_zone]);

            $validator = Validator::make((array) $params, [
                'delivery_date' => 'required|date',
                'delivery_type_id' => 'required|integer|exists:delivery_types,id',
                'time_slot_id' => 'required|integer|exists:time_slots,id',
            ], [
                'delivery_date.required' => 'Please select a delivery date.',
                'delivery_type_id.required' => 'Please select a delivery type.',
                'time_slot_id.required' => 'Please select a delivery time slot.',
            ]);

            if ($validator->fails()) {
                return response()->json(new Response($request->token, ['form' => Utils::formatErrors($validator->errors()->messages())], 201));
            }

            if ($request->user('user')) {
                $userable_type = User::class;
                $user = $request->user('user');
            } else {
                $userable_type = GuestUser::class;
                $user = GuestUser::where('user_token', $request->user_token)->first();
                if (!$user) {
                    $user = GuestUser::create(["user_token" => $request->user_token]);
                }
            }

            $delivery_type = DeliveryType::findOrFail($params->delivery_type_id);
            $time_slot = TimeSlot::findOrFail($params->time_slot_id);

            $userDeliveryType = UserDeliveryType::create([
                'delivery_date' => $params->delivery_date,
                'delivery_type_id' => $params->delivery_type_id,
                'time_slot_id' => $params->time_slot_id,
                'delivery_price' => $delivery_type->price,
                'delivery_time' => $time_slot->slot_name,
                'userable_type' => $userable_type,
                'userable_id' => $user->id
            ]);

            $payment = Payment::first();

            if ((int) $payment->cash_on_delivery != 1 && ((int) $request->order_method == Config::get('constants.paymentMethod.CASH_ON_DELIVERY'))) {
                return response()->json(Validation::error(
                    $request->token,
                    __('lang.accepted_cod', [], $lang)
                ));

            }
            if ((int) $payment->paypal != 1 && (int) $request->order_method == Config::get('constants.paymentMethod.PAYPAL')) {
                return response()->json(Validation::error(
                    $request->token,
                    __('lang.accepted_paypal', [], $lang)
                ));
            }
            if ((int) $payment->stripe != 1 && (int) $request->order_method == Config::get('constants.paymentMethod.STRIPE')) {
                return response()->json(Validation::error(
                    $request->token,
                    __('lang.accepted_gateway', [], $lang)
                ));
            }
            if ((int) $payment->razorpay != 1 && (int) $request->order_method == Config::get('constants.paymentMethod.RAZORPAY')) {
                return response()->json(Validation::error(
                    $request->token,
                    __('lang.accepted_gateway', [], $lang)
                ));
            }
            if ((int) $payment->iyzico_payment != 1 && (int) $request->order_method == Config::get('constants.paymentMethod.IYZICO_PAYMENT')) {
                return response()->json(Validation::error(
                    $request->token,
                    __('lang.accepted_gateway', [], $lang)
                ));
            }
            if ((int) $payment->bank != 1 && (int) $request->order_method == Config::get('constants.paymentMethod.BANK')) {
                return response()->json(Validation::error(
                    $request->token,
                    __('lang.accepted_gateway', [], $lang)
                ));
            }
            if ((int) $payment->payfast_payment != 1 && (int) $request->order_method == Config::get('constants.paymentMethod.PAYFAST')) {
                return response()->json(Validation::error(
                    $request->token,
                    __('lang.accepted_gateway', [], $lang)
                ));
            }
            if ((int) $payment->telr != 1 && (int) $request->order_method == Config::get('constants.paymentMethod.TELR')) {
                return response()->json(Validation::error(
                    $request->token,
                    __('lang.accepted_gateway', [], $lang)
                ));
            }

            $validate = Validation::order($request);
            if ($validate) {
                return response()->json($validate);
            }
            $user = $request->user('user');
            $cartQuery = Cart::query();
            if ($user) {
                $cartQuery = $cartQuery->where('user_id', $request->user('user')->id);
            } else if ($request->user_token) {
                $setting = Setting::select('guest_checkout')->first();
                if (!$setting->guest_checkout) {
                    return response()->json(Validation::unauthorized());
                }
                $cartQuery = $cartQuery->where('user_token', $request->user_token);
            } else {
                return response()->json(Validation::errorLang($lang));
            }

            $existingCart = $cartQuery->with([
                'product_inner.admin',
                'shipping_place.shipping_rule',
                'userDeliveryType.deliveryType'
            ])
                ->where('selected', Config::get('constants.status.PUBLIC'))
                ->with('updated_inventory')
                ->get();

            $totalPriceWithoutShipping = 0;
            $shippingPriceP = 0;

            if ($userDeliveryType){
                foreach ($existingCart as $key => $cart) {
                    $shippingPriceP = $userDeliveryType->delivery_price ?? 0;

                    if (!is_null($cart->product_inner)) {
                        // Selling price calculation

                        if (count($cart->updated_inventory->inventory_attributes) > 0) {
                            $inventoryPrice = (float) $cart->updated_inventory->price;
                        } else {
                            $inventoryPrice = 0;
                        }

                        $selling = (float) $cart->product_inner->selling;
                        $offered = (float) $cart->product_inner->offered;
                        $flashPrice = 0;
                        if (!is_null($cart->product_inner->end_time)) {
                            $flashPrice = (float) $cart->product_inner->price;
                        }
                        if ($inventoryPrice > 0) {
                            $currentPrice = $inventoryPrice;
                        } else if ($flashPrice > 0) {
                            $currentPrice = $flashPrice;
                        } else if ($offered > 0) {
                            $currentPrice = $offered;
                        } else {
                            $currentPrice = $selling;
                        }
                        // Bundle calculation
                        $bundleQtyOffer = 0;
                        $bundleDeal = $cart->product_inner->bundle_deal;
                        if ($bundleDeal) {
                            if ($cart->quantity >= $bundleDeal->buy) {
                                $bundleQtyOffer = $bundleDeal->free;
                            }
                        }
                        $totalPriceWithoutShipping += (float) $currentPrice * ((int) $cart->quantity - $bundleQtyOffer);
                    }
                }
            }

            $offeredVoucher = 0;
            $voucher = null;

            if ($request->voucher) {
                $voucher = Voucher::where('code', $request->voucher)
                    ->where('status', Config::get('constants.status.PUBLIC'))
                    ->get()->first();

                if (is_null($voucher)) {
                    return response()->json(Validation::error(
                        $request->token,
                        __('lang.invalid_voucher', [], $lang)
                    ));
                }

                if ($totalPriceWithoutShipping < $voucher->min_spend) {
                    $setting = Setting::select('currency', 'currency_icon', 'currency_position')->first();
                    $price = $voucher->min_spend . $setting->currency_icon;

                    if ((int) $setting->currency_position == Config::get('constants.currencyPosition.PRE')) {
                        $price = $setting->currency_icon . $voucher->min_spend;
                    }

                    return response()->json(Validation::error(
                        $request->token,
                        __('lang.min_spent', ['amount' => $price], $lang)
                    ));
                }

                $totalOrdered = Order::where('voucher_id', $voucher->id)->count();

                if ($totalOrdered >= $voucher->usage_limit) {
                    return response()->json(Validation::error(
                        $request->token,
                        __('lang.voucher_exceeded', [], $lang)
                    ));
                }
                $OrderedByUserQuery = Order::where('voucher_id', $voucher->id);

                if ($request->user('user')) {
                    $OrderedByUserQuery = $OrderedByUserQuery->where('user_id', $request->user('user')->id);
                } else if ($request->user_token) {
                    $OrderedByUserQuery = $OrderedByUserQuery->where('user_token', $request->user_token);
                } else {
                    return response()->json(Validation::errorLang($lang));
                }

                $totalOrderedByUser = $OrderedByUserQuery->count();

                if ($totalOrderedByUser >= $voucher->limit_per_customer) {
                    return response()->json(Validation::error(
                        $request->token,
                        __('lang.voucher_max', [], $lang)
                    ));
                }

                $start = new Carbon($voucher->start_time);
                $end = new Carbon($voucher->end_time);
                $now = Carbon::now();

                if ($start >= $now && $now >= $end) {
                    return response()->json(Validation::error(
                        $request->token,
                        __('lang.voucher_expired', [], $lang)
                    ));
                }

                if ((int) $voucher->type === (int) Config::get('constants.priceType.FLAT')) {
                    $offeredVoucher = $voucher->price;
                } else {
                    $offeredVoucher = number_format((float) ($voucher->price * $totalPriceWithoutShipping) / 100, 2, '.', '');
                }
                if (!is_null($voucher->capped_price) && $offeredVoucher > $voucher->capped_price) {
                    $offeredVoucher = (int) $voucher->capped_price;
                }
            }

            $cartError = [];

            foreach ($existingCart as $c) {
                $productErr = [];
                $error = false;

                if ($c->product->status != Config::get('constants.status.PUBLIC')) {
                    $productErr[] = __('lang.private_product', ['product' => $c->product->title], $lang);
                    $error = true;
                }
                if ((int) $c->updated_inventory->quantity < 1) {
                    $productErr[] = __('lang.out_stock_product', ['product' => $c->product->title], $lang);
                    $error = true;
                }
                if ($error) {
                    $cartError[$c->id] = $productErr;
                }
            }

            if (count($cartError) > 0) {
                return response()->json(Validation::error($request->token, $cartError, 'product'));
            }

            $setting = Setting::select('currency')->first();

            if (!$voucher) {
                $voucher['id'] = null;
            }

            if (count($existingCart) > 0 && $userDeliveryType) {
                $userDeliveryTypeId = $userDeliveryType->id;
                $now = Carbon::now();
                $orderArr = [
                    'order_method' => $request->order_method,
                    'voucher_id' => $voucher['id'],
                    'currency' => $setting->currency,
                    'updated_at' => $now,
                    'created_at' => $now,
                    'user_delivery_type_id' => $userDeliveryTypeId,
                ];

                if ($request->user('user')) {
                    $orderArr['user_id'] = $request->user('user')->id;
                    $orderArr['order'] = Utils::generateTrackingId(["user_id" => $request->user('user')->id]);
                    $orderArr['user_address_id'] = $user->default_address;
                } else if ($request->user_token) {
                    $guestUser = GuestUser::where('user_token', $request->user_token)->first();
                    if (!$guestUser) {
                        GuestUser::create(["user_token" => $request->user_token]);
                    }

                    if (!$guestUser->default_address) {
                        $userAddress = UserAddress::where('user_token', $request->user_token)->first();
                        GuestUser::where('user_token', $request->user_token)
                            ->update(['default_address' => $userAddress->id]);
                        $guestUser = GuestUser::where('user_token', $request->user_token)->first();

                    }
                    $orderArr['user_address_id'] = $guestUser->default_address;
                    $orderArr['user_token'] = $request->user_token;
                    $orderArr['order'] = Utils::generateTrackingId(["user_id" => rand(2, 50)]);
                }

                $order = Order::create($orderArr);
                $totalPrice = 0;
                $result = false;

                foreach ($existingCart as $key => $cart) {
                    if (!is_null($cart->product_inner)) {
                        // Selling price calculation
                        if (count($cart->updated_inventory->inventory_attributes) > 0) {
                            $inventoryPrice = (float) $cart->updated_inventory->price;
                        } else {
                            $inventoryPrice = 0;
                        }
                        $selling = (float) $cart->product_inner->selling;
                        $offered = (float) $cart->product_inner->offered;
                        $flashPrice = null;
                        if (!is_null($cart->product_inner->end_time)) {
                            $flashPrice = (float) $cart->product_inner->price;
                        }
                        if ($inventoryPrice > 0) {
                            $currentPrice = $inventoryPrice;
                        } else if ($flashPrice !== null) {
                            $currentPrice = $flashPrice;
                        } else if ($offered > 0) {
                            $currentPrice = $offered;
                        } else {
                            $currentPrice = $selling;
                        }

                        // Bundle calculation
                        $bundleQtyOffer = 0;
                        $bundleDeal = $cart->product_inner->bundle_deal;
                        if ($bundleDeal) {
                            if ($cart->quantity >= $bundleDeal->buy) {
                                $bundleQtyOffer = $bundleDeal->free;
                            }
                        }

                        // Tax calculation
                        $taxQtyOffer = 0;
                        $taxRule = $cart->product_inner->tax_rules;
                        if ($taxRule) {
                            if ((int) $taxRule->type === (int) Config::get('constants.priceType.FLAT')) {
                                $taxQtyOffer = $taxRule->price;
                            } else {
                                $taxQtyOffer = number_format(
                                    (float) ($taxRule->price * $currentPrice) / 100,
                                    2,
                                    '.',
                                    ''
                                );
                            }
                        }

                        $totalTax = (float) ($taxQtyOffer * (int) $cart->quantity);
                        $priceWithoutBundle = (float) ($currentPrice * ((int) $cart->quantity - (int) $bundleQtyOffer));
                        $total = (float) ($totalTax + $priceWithoutBundle);

                        $totalPrice += $total;
                        $commission = $cart->product_inner->admin->commission || 0;

                        // Inserting ordered product
                        $orderedProducts = [
                            'commission' => $commission,
                            'tax_price' => $taxQtyOffer,
                            'commission_amount' => ($currentPrice * $cart->quantity * $commission) / 100,
                            'product_id' => $cart->product_inner->id,
                            'inventory_id' => $cart->inventory_id,
                            'quantity' => $cart->quantity,
                            'shipping_place_id' => $cart->shipping_place_id,
                            'shipping_type' => $cart->shipping_type,
                            'purchased' => $cart->product_inner->purchased,
                            'bundle_offer' => $bundleQtyOffer,
                            'shipping_price' => 0,
                            'selling' => $currentPrice,
                            'withdrawn' => Config::get('constants.withdrawn.NO'),
                            'order_id' => $order->id,
                            'updated_at' => $now,
                            'created_at' => $now
                        ];

                        $orderedProduct = OrderedProduct::create($orderedProducts);

                        if ($cart->note) {
                            OrderedProductNote::create([
                                'ordered_product_id' => $orderedProduct->id,
                                'message' => $cart->note->message,
                                'image' => $cart->note->image,
                            ]);
                        }

                        UpdatedInventory::where('id', $cart->inventory_id)->decrement('quantity', $cart->quantity);

                        $result = true;
                    }
                }

                if ($result) {
                    $totalPrice = number_format($totalPrice, 2, '.', '');
                    $re['currency'] = $setting->currency;
                    $re['amount'] = number_format((float) $totalPrice - $offeredVoucher, 2, '.', '');
                    $re['id'] = $order->id;
                    $re['order'] = $order->order;
                    $totalPrice += $shippingPriceP;

                    if ($request->user('user')) {
                        $user = $request->user('user');
                        Cart::where('user_id', $user->id)
                            ->where('selected', Config::get('constants.status.PUBLIC'))
                            ->delete();

                        $re['name'] = $user->name;
                        $re['email'] = $user->email;

                    } else if ($request->user_token) {
                        Cart::where('user_token', $request->user_token)
                            ->where('selected', Config::get('constants.status.PUBLIC'))
                            ->delete();
                        $guestUser = GuestUser::where('user_token', $request->user_token)->first();
                        $re['name'] = $guestUser->name;
                        $re['email'] = $guestUser->email;
                    }


                    if ((int) $request->order_method == Config::get('constants.paymentMethod.STRIPE')) {
                        $re['order_method'] = Config::get('constants.paymentMethod.STRIPE');
                        // Saving the total amount to generate a report for admin easily
                        Order::where('id', $order->id)->update([
                            'total_amount' => $totalPrice - $offeredVoucher,
                        ]);
                        return response()->json(new Response($request->token, $re));
                    }
                    if ((int) $request->order_method == Config::get('constants.paymentMethod.RAZORPAY')) {
                        try {
                            $api = new Api($payment->razorpay_key, $payment->razorpay_secret);
                            $razorpayOrder = $api->order->create([
                                'receipt' => 'order_id_' . $order->id,
                                'amount' => ($totalPrice - $offeredVoucher) * 100,
                                'currency' => $setting->currency
                            ]);
                            $re['payment_token'] = $razorpayOrder->id;
                            $re['order_method'] = Config::get('constants.paymentMethod.RAZORPAY');

                            // Saving the total amount to generate a report for admin easily
                            Order::where('id', $order->id)->update([
                                'payment_token' => $razorpayOrder->id,
                                'total_amount' => $totalPrice - $offeredVoucher,
                            ]);

                            return response()->json(new Response($request->token, $re));
                        } catch (Exception $e) {
                            $this->rollbackOrderToInventory($existingCart, $order);
                            return response()->json(Validation::error($request->token, $e->getMessage()));
                        }
                    }
                    if ((int) $request->order_method == Config::get('constants.paymentMethod.CASH_ON_DELIVERY')) {
                        // Saving the total amount to generate a report for admin easily
                        Order::where('id', $order->id)->update([
                            'total_amount' => $totalPrice - $offeredVoucher,
                        ]);
                        return response()->json(new Response($request->token, $order));
                    }
                    if ((int) $request->order_method == Config::get('constants.paymentMethod.BANK')) {
                        // Saving the total amount to generate a report for admin easily
                        Order::where('id', $order->id)->update([
                            'total_amount' => $totalPrice - $offeredVoucher,
                        ]);
                        return response()->json(new Response($request->token, $order));
                    }
                    if ((int) $request->order_method == Config::get('constants.paymentMethod.PAYPAL')) {
                        // Saving the total amount to generate a report for admin easily
                        Order::where('id', $order->id)->update([
                            'total_amount' => $totalPrice - $offeredVoucher,
                        ]);
                        return response()->json(new Response($request->token, $re));
                    }
                    if ((int) $request->order_method == Config::get('constants.paymentMethod.FLUTTERWAVE')) {
                        // Saving the total amount to generate a report for admin easily
                        Order::where('id', $order->id)->update([
                            'total_amount' => $totalPrice - $offeredVoucher,
                        ]);
                        return response()->json(new Response($request->token, $re));
                    }
                    if ((int) $request->order_method == Config::get('constants.paymentMethod.IYZICO_PAYMENT')) {
                        // Saving the total amount to generate a report for admin easily
                        Order::where('id', $order->id)->update([
                            'total_amount' => $totalPrice - $offeredVoucher,
                        ]);
                        $re["iyzico_payment"] = IyzicoPayment::initIyzico($request, $order->id);
                        return response()->json(new Response($request->token, $re));
                    }
                    if ((int) $request->order_method == Config::get('constants.paymentMethod.PAYFAST')) {
                        // Saving the total amount to generate a report for admin easily
                        Order::where('id', $order->id)->update([
                            'total_amount' => $totalPrice - $offeredVoucher,
                        ]);
                        $re['payfast'] = PayFast::getPayFastForm($payment, $order, $re, $totalPrice - $offeredVoucher);
                        return response()->json(new Response($request->token, $re));
                    }
                    if ((int) $request->order_method == Config::get('constants.paymentMethod.TELR')) {
                        // Saving the total amount to generate a report for admin easily
                        Order::where('id', $order->id)->update([
                            'total_amount' => $totalPrice - $offeredVoucher,
                        ]);

                        if(app()->isProduction()) {
                            $telr = new TelrPaymentService();
                            $order = $order->fresh();
                            $telrRes = $telr->createPaymentSession($order);

                            if (!$telrRes['success']) {
                                return response()->json(
                                    Validation::error(
                                        $request->token,
                                        __("Payment gateway session creation failed: " . ($telrRes['message'] ?? 'Unknown error'))
                                    )
                                );
                            }
                            $re['telr'] = ['ref' => $telrRes['data']['order_ref'] ?? null, 'url' => $telrRes['data']['payment_url'] ?? null];
                            return response()->json(new Response($request->token, $re));
                        }

                        return response()->json(new Response($request->token, $order));

                    }
                }
                return response()->json(Validation::error($request->token, __('lang.went_wrong', [], $lang)));
            }

            return response()->json(Validation::error($request->token, __('lang.no_cart', [], $lang)));

        } catch (Exception $e) {
            return response()->json(Validation::error($request->token, $e->getMessage()));
        }
    }

    public function createTelrPaymentRef(Request $request)
    {
        $params = $request->data ? json_decode(Utils::jsDecryption($request->data)) : (object) $request->all();
        $order = Order::findOrFail($params->order_id);

        try {
            $telr = new TelrPaymentService();
            $telrRes = $telr->createPaymentSession($order);

            if (!$telrRes['success']) {
                return response()->json(
                    Validation::error($request->token, "Payment gateway session creation failed: " . ($telrRes['message'] ?? 'Unknown error'))
                );
            }
            $result = ['ref' => $telrRes['data']['order_ref'], 'url' => $telrRes['data']['payment_url']];
            return response()->json(new Response($request->token, $result));
        } catch (Exception $e) {
            return response()->json(Validation::error($request->token, $e->getMessage()));
        }
    }

    public function telrPaymentUpdate(Request $request): JsonResponse
    {
        $params = json_decode(Utils::jsDecryption($request->data));
        $orderRef = $params->OrderRef;
        $payId = $params->PAYID ?? null;

        try {
            $telr = new TelrPaymentService();

            $checkRes = $telr->checkPaymentStatus($orderRef, $payId);

            if (!$checkRes['success']) {
                return response()->json(
                    Validation::error($request->token, $checkRes['message'] ?? __('Payment status check failed.'))
                );
            }

            $statusCode = (int) ($checkRes['data']['status_code'] ?? 0);
            $telrOrder = $checkRes['data']['telr_order'] ?? [];
            $tranRef = $checkRes['data']['transaction_ref'] ?? null;
            $orderModel = $checkRes['data']['order'] ?? null;

            if (!$orderModel) {
                return response()->json(Validation::error($request->token, __('Invalid order id.')));
            }

            switch ($statusCode) {

                case 1:
                    return response()->json(new Response($request->token, [
                        'payment_state' => 'pending',
                        'ref' => $orderRef,
                        'order' => $orderModel->fresh(),
                        'message' => __('Payment is pending.')
                    ]));

                case 3: // Paid
                    if (!$orderModel->payment_done) {
                        $orderModel->update([
                            'payment_done' => true,
                            'order_method' => config('constants.paymentMethod.TELR'),
                            'trans_id' => $tranRef,
                            'payment_token' => $orderRef,
                        ]);
                    }

                    return response()->json(new Response($request->token, [
                        'payment_state' => 'completed',
                        'order' => $orderModel->fresh(),
                        'message' => __('Payment completed successfully.')
                    ]));

                case -1:
                case -2:
                case -3:
                    // Create a new session so the frontend can retry
                    $createRes = $telr->createPaymentSession($orderModel->fresh());

                    if (!$createRes['success']) {
                        return response()->json(
                            Validation::error($request->token, "Payment gateway session creation failed: " . ($createRes['message'] ?? 'Unknown error'))
                        );
                    }

                    $stateMap = [
                        -1 => 'expired',
                        -2 => 'cancelled',
                        -3 => 'declined',
                    ];

                    $paymentState = $stateMap[$statusCode] ?? 'unknown';
                    $message = "Payment session got {$paymentState}.";

                    if ($paymentState === 'declined') {
                        $tranMsg = $telrOrder['transaction']['message'] ?? null;
                        $message = $tranMsg
                            ? "Payment failed due to: {$tranMsg}"
                            : "Payment was declined by customer.";
                    }

                    return response()->json(new Response($request->token, [
                        'payment_state' => $paymentState,
                        'order' => $orderModel->fresh(),
                        'message' => __($message),
                        'ref' => $createRes['data']['order_ref'] ?? null,
                        'url' => $createRes['data']['payment_url'] ?? null,
                    ]));

                default:
                    return response()->json(
                        Validation::error($request->token, __('Payment failed or declined.'))
                    );
            }

        } catch (Exception $e) {
            return response()->json(Validation::error($request->token, $e->getMessage()));
        }
    }

    private function rollbackOrderToInventory($existingCart, $order)
    {
        foreach ($existingCart as $ops) {
            OrderedProduct::where('order_id', $order->id)->delete();
            UpdatedInventory::where('id', $ops->inventory_id)->increment('quantity', $ops->quantity);
        }
        Order::where('id', $order->id)->delete();
    }

    public function payFastNotify(Request $request)
    {
        $lang = $request->header('language');
        $orderId = $request->m_payment_id;

        $orderDetails = Order::find($orderId);
        $payment = Payment::first();

        $pfParamString = '';
        foreach ($request->all() as $key => $val) {
            if ($key !== 'signature') {
                $pfParamString .= $key . '=' . urlencode($val) . '&';
            } else {
                break;
            }
        }

        $check1 = PayFast::pfValidSignature($request->all(), $pfParamString, $payment->payfast_passphrase);
        $check2 = PayFast::pfValidIP();
        $cartTotal = $orderDetails->total_amount;
        $check3 = PayFast::pfValidPaymentData($cartTotal, $request->all());
        $check4 = PayFast::pfValidServerConfirmation($pfParamString, $payment->payfast_base_url);


        if (!$check1) {
            return response()->json(Validation::error(
                $request->token,
                __('lang.invalid_sig', [], $lang)
            ))->setStatusCode(496);
        }

        if (!$check2) {
            return response()->json(Validation::error(
                $request->token,
                __('lang.invalid_ip', [], $lang)
            ))->setStatusCode(497);
        }

        if (!$check3) {
            return response()->json(Validation::error(
                $request->token,
                __('lang.invalid_amount', [], $lang)
            ))->setStatusCode(498);
        }

        if (!$check4) {
            return response()->json(Validation::error(
                $request->token,
                __('lang.invalid_validation', [], $lang)
            ))->setStatusCode(499);
        }

        Order::where('id', $orderId)->update([
            'payment_done' => true
        ]);

        return response()->json(new Response($request->token, true));
    }

    public function sendOrderEmail(Request $request, $id)
    {
        try {
            $lang = $request->header('language');

            $mailDataLang = MailHelper::sendingOrderEmail($request, $id, $lang);
            $mailData = MailHelper::sendingOrderEmail($request, $id);

            if (is_null($mailData)) {
                return response()->json(Validation::error($request->token, __('lang.invalid_order', [], $lang)));
            }

            if (!$mailData) {
                return response()->json(new Response($request->token, false, 422, 'Mail data is not valid.'));
            }

            $setting = $mailData['setting'];
            $order = $mailData['order'];

            $userName = "";
            $userEmail = "";

            if ($request->user('user')) {

                $userEmail = $order->user->email;
                $userName = $order->user->name;

            } else if ($request->user_token) {
                if ($order->guest_user->email) {
                    $userEmail = $order->guest_user->email;
                }

                if ($order->guest_user->name) {
                    $userName = $order->guest_user->name;
                }
            } else {
                return response()->json(Validation::errorLang($lang));
            }

            $adminSetting = Setting::select('attach_pdf', 'send_seller_email', 'translate_pdf')->first();

            $fetchOrder = Order::find($id);

            $allowAdminEmail =
                $fetchOrder->payment_done == Config::get('constants.paymentStatus.COMPLETED') ||
                (
                    $fetchOrder->payment_done == Config::get('constants.paymentStatus.PENDING') &&
                    $fetchOrder->order_method == Config::get('constants.paymentMethod.CASH_ON_DELIVERY')
                );

            if ($adminSetting->send_seller_email && $allowAdminEmail) {
                $orderedPrQ = OrderedProduct::query();

                if ($lang) {
                    $orderedPrQ = $orderedPrQ->with([
                        'product_with_admin' => function ($query) use ($lang) {
                            $query->with('admin');
                            $query->leftJoin(
                                'product_langs as pl',
                                function ($join) use ($lang) {
                                    $join->on('products.id', '=', 'pl.product_id');
                                    $join->where('pl.lang', $lang);
                                }
                            )
                                ->select(
                                    'products.id',
                                    'products.title',
                                    'products.image',
                                    'products.selling',
                                    'products.offered',
                                    'products.shipping_rule_id',
                                    'products.admin_id',
                                    'products.bundle_deal_id',
                                    'products.unit',
                                    'pl.title'
                                );
                        }
                    ]);
                    $orderedPrQ = $orderedPrQ->with([
                        'updated_inventory.inventory_attributes.attribute_value' => function ($query) use ($lang) {
                            $query->leftJoin(
                                'attribute_value_langs as avl',
                                function ($join) use ($lang) {
                                    $join->on('attribute_values.id', '=', 'avl.attribute_value_id');
                                    $join->where('avl.lang', $lang);
                                }
                            )
                                ->with([
                                    'attribute' => function ($query) use ($lang) {

                                        $query->leftJoin(
                                            'attribute_langs as al',
                                            function ($join) use ($lang) {
                                                $join->on('attributes.id', '=', 'al.attribute_id');
                                                $join->where('al.lang', $lang);
                                            }
                                        )
                                            ->select('attributes.id', 'attributes.title', 'al.title');
                                    }
                                ])
                                ->select('attribute_values.*', 'avl.title');
                        }
                    ]);
                } else {
                    $orderedPrQ = $orderedPrQ->with('product_with_admin.admin')
                        ->with('updated_inventory.inventory_attributes.attribute_value.attribute');
                }

                $orderedPr = $orderedPrQ->with('shipping_place')
                    ->where('order_id', $id)
                    ->get();

                $adminProducts = [];

                foreach ($orderedPr as $op) {
                    $adminEmail = $op->product_with_admin->admin->email;

                    if (!key_exists($adminEmail, $adminProducts)) {
                        $adminProducts[$adminEmail] = [];
                    }
                    $adminProducts[$adminEmail][] = $op;
                }

                $orderP = clone $mailDataLang['order'];
                unset($orderP['ordered_products']);
                unset($orderP['setting']);
                $mailDataLangClone['order'] = $orderP;

                foreach ($adminProducts as $key => $value) {
                    $adminName = $value[0]->product_with_admin->admin->name;

                    $r1 = $mailDataLangClone['order'];
                    $r1['ordered_products'] = $value;

                    $res['setting'] = clone $mailDataLang['setting'];
                    $res['order'] = $r1;
                    $res['lang'] = $lang;
                    $res['setting']->receiver = $adminName;

                    //return response()->json(new Response($request->token, $res));

                    Mail::send(
                        'mail_templates.order_placed_to_vendor',
                        $res,
                        function ($message) use ($setting, $order, $lang, $value, $key, $adminName) {
                            $message->to($key, $adminName)
                                ->subject(__('lang.new_order', [], $lang));
                        }
                    );
                }
            }

            if ($adminSetting->attach_pdf) {

                if ($adminSetting->translate_pdf) {
                    $html = View::make('mail_templates.order_pdf', $mailDataLang)
                        ->render();
                } else {
                    $html = View::make('mail_templates.order_pdf', $mailData)->render();
                }

                $pdf = new Mpdf(['tempDir' => storage_path('app/public/uploads')]);
                $pdf->autoScriptToLang = true;
                $pdf->autoLangToFont = true;
                $pdf->WriteHTML($html);

                Mail::send(
                    'mail_templates.order_placed',
                    $mailDataLang,
                    function ($message) use ($setting, $pdf, $order, $lang, $userName, $userEmail) {
                        $message->to($userEmail, $userName)
                            ->subject(
                                __('lang.confirmation', ['store' => $setting->store_name], $lang)
                            )
                            ->attachData($pdf->Output('', 'S'), $order['order'] . ".pdf");
                    }
                );

            } else {

                if ($fetchOrder->status ==  Config::get('constants.status.PUBLIC') && $fetchOrder->payment_done == Config::get('constants.paymentStatus.PENDING')){
                    $subject = __('lang.pending_order_subject');
                }else{
                    $subject = __('lang.confirmation', ['store' => $setting->store_name], $lang);
                }
                Mail::send(
                    'mail_templates.order_placed',
                    $mailDataLang,
                    function ($message) use ($setting, $order, $lang, $userName, $userEmail, $subject) {
                        $message->to($userEmail, $userName)->subject($subject);
                    }
                );

            }
        } catch (Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
        return response()->json(new Response($request->token, true));
    }

    public function sendDeliveredEmail(Request $request, $id): JsonResponse
    {
        try {
            $lang = $request->header('language');

            $mailData = MailHelper::sendingOrderEmail($request, $id, $lang);
            if (is_null($mailData)) {
                return response()->json(Validation::error(
                    $request->token,
                    __('lang.invalid_order', [], $lang)
                ));
            }

            if ((int) Config::get('constants.orderStatus.DELIVERED') != (int) $mailData['order']['status']) {
                return response()->json(new Response($request->token, true));
            }

            $order = $mailData['order'];

            $name = $order->user['name'] ?: '';
            $email = $order->user['email'] ?: null;

            if (is_null($email)) {
                return response()->json(Validation::error(
                    $request->token,
                    __('lang.no_user', [], $lang)
                ));
            }

            Mail::send(
                'mail_templates.package_delivered',
                $mailData,
                function ($message) use ($order, $email, $name, $lang) {
                    $message->to($email, $name)
                        ->subject(__('lang.package_delivered', [], $lang));
                }
            );


        } catch (Exception $ex) {
            return response()->json(Validation::error($request->token, explode('.', $ex->getMessage())[0]));
        }

        return response()->json(new Response($request->token, true));
    }

    public function checkOrder(Request $request): JsonResponse
    {
        try {
            $existing = Licence::where('public_key', $request->public_key)
                ->where('secret_key', $request->secret_key)
                ->where('encrypt_iv', $request->encrypt_iv)
                ->where('encrypt_key', $request->encrypt_key)
                ->first();

            if (is_null($existing)) {
                return response()->json(Validation::nothing_found());
            }
            if (Licence::where('id', $existing->id)->delete()) {
                return response()->json(new Response(null, true));
            }

            return response()->json(Validation::error());


        } catch (Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }

    }

    public function generatePDF($id)
    {
        if ($this->isVendor) {
            return Utils::isDataOwner(null, null);
        }

        if ($can = Utils::userCan($this->user, 'order.view')) {
            return $can;
        }

        if ($can = Utils::userCan($this->user, 'order.edit')) {
            return $can;
        }

        $key = hex2bin("0123456470abcdef0123456789abcdef");
        $iv = hex2bin("abcdef1876343516abcdef9876543210");

        $encrypted = '3z8tIolfpCM8iqPnvDbv3w==';
        $decrypted = openssl_decrypt($encrypted, 'AES-128-CBC', $key, OPENSSL_ZERO_PADDING, $iv);

        $decrypted = trim($decrypted);

        dd($decrypted);


        $order = MailHelper::order($id);
        $objDemo = MailHelper::emailData('jj');
        $objDemo->logo_base64 = FileHelper::imageToBase64($objDemo->image);

        //return view('mail_templates.package_delivered', ['order' => $order, 'setting' => $objDemo]);
        // return view('mail_templates.order_placed', ['order' => $order, 'setting' => $objDemo]);
        //return view('mail_templates.order_pdf', ['order' => $order, 'setting' => $objDemo]);

        $pdf = \PDF::loadView('mail_templates.order_pdf', ['order' => $order, 'setting' => $objDemo])
            ->setPaper('a4', 'potrait')->setWarnings(false);
        return $pdf->download('disney.pdf');
    }

    //    public function testSync(syncTsShopDataService $syncService)
//    {
//        // Call the service method
//        $data = $syncService->syncTsShopData();
//
//        // Dump the result
//        dd($data);
//    }
}
