<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserDeliveryTypeRequest;
use App\Models\GuestUser;
use App\Models\Cart;
use App\Models\Helper\Response;
use App\Models\Helper\Validation;
use App\Models\User;
use App\Models\UserDeliveryType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UserDeliveryTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $date = $request->get('date');

        if (!$date || Carbon::parse($date)->lt(Carbon::today())) {
            return response()->json(new Response($request->token, []));
        }

        $isToday = ($request->get('date') === date('Y-m-d'));
        $currentDate = Carbon::now();
        $currentTime = Carbon::now()->format('H:i:s');
        $lang = request()->header('X-lang') ?? app()->getLocale();

        if (Carbon::parse($request->get('date'))->isFuture()) {
            $currentDate = Carbon::now()->startOfDay();
        }

        $user = request()->user('user');

        $hasTodayDeliveryProduct = false; //non today

        $cartQuery = Cart::query()
            ->when($user?->id, fn($q) => $q->where('user_id', $user->id))
            ->when(!$user?->id && $request->user_token, fn($q) => $q->where('user_token', $request->user_token))
            ->where('selected', true);

        $hasProduct = $cartQuery->exists();

        if ($hasProduct) {
            $hasStandardProduct = (clone $cartQuery)
                ->whereHas('product', fn($q) => $q->where('available_for_delivery_today', '=', false))
                ->exists();

            $hasTodayDeliveryProduct = !$hasStandardProduct;
        }

        $query = DB::table('delivery_types as dt')
            ->join('time_slots as ts', 'dt.id', '=', 'ts.delivery_type_id')
            ->select(
                'dt.id as delivery_type_id',
                DB::raw("COALESCE(
                    NULLIF(dt.type_name->>'$.$lang', ''),
                    JSON_UNQUOTE(JSON_EXTRACT(dt.type_name, CONCAT('$.', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(dt.type_name), '$[0]')))))
                ) as type_name"),
                'dt.price',
                'dt.currency',
                'dt.cutoff_time',
                'ts.id as slot_id',
                'ts.slot_name',
                'ts.start_time',
                'ts.end_time',
                'ts.available_if_before',
            )
            ->where('dt.is_active', true)
            ->where('ts.is_active', true);

        $results = $query->when($isToday && !$hasTodayDeliveryProduct, function ($query) use ($currentTime) {
            $query->where('dt.available_for_today', true)
                ->where(function ($q) use ($currentTime) {
                    $q->whereNull('dt.cutoff_time')
                        ->orWhere('dt.cutoff_time', '>', $currentTime);
                });
        })->orderBy('dt.display_order')
            ->where('state', $request->get('state'))
            ->orderBy('ts.display_order')
            ->get();

        $grouped = $results->groupBy('delivery_type_id')->map(function ($slots) use ($currentDate, $isToday) {
            $first = $slots->first();

            return [
                'delivery_type_id' => $first->delivery_type_id,
                'type_name' => $first->type_name,
                'price' => $first->price,
                'currency' => $first->currency,
                'time_slots' => $slots->map(function ($slot) use ($currentDate, $isToday) {

                    if ($isToday) {
                        if ($currentDate->format('H:i:s') > $slot->available_if_before) {
                            return null;
                        }
                    }

                    return [
                        'slot_id' => $slot->slot_id,
                        'slot_name' => $slot->slot_name,
                        'start_time' => $slot->start_time,
                        'end_time' => $slot->end_time,
                    ];
                })->filter()
                ->values(),
            ];
        })->filter(fn($deliveryType) => $deliveryType['time_slots']->isNotEmpty())->values();

        return response()->json(new Response($request->token, $grouped));
    }

    public function store(UserDeliveryTypeRequest $request): JsonResponse
    {

        $data = $request->except('user_id', 'cart_ids');

        try {
            $type = UserDeliveryType::create($data);

            $this->updateOrCreateCart($request->get('cart_ids'), $type->id);

            $type->load([
                'deliveryType' => function ($query) {
                    $query->select('id', 'type_name', 'price', 'currency');
                },
                'timeSlot' => function ($query) {
                    $query->select('id', 'slot_name');
                },
                'userable' => function ($query) {
                    $query->select('id', 'name');
                },
                'cart' => function ($query) {
                    $query->select('id', 'quantity');
                }
            ]);


        } catch (\Exception $exception) {
            return response()->json(Validation::error($request->token, $exception->getMessage()));
        }

        return response()->json(new Response(token: $request->token, data: $type, message: "You have successfully added delivery type."));
    }

    public function getUserDeliveryTypeByUserId(Request $request): \Illuminate\Http\JsonResponse
    {

        $user = request()->user('user')
                ?->load('userDeliveryTypes');

        if (!$user) {
            $user = GuestUser::query()
                ->with('userDeliveryTypes')
                ->where('user_token', $request->user_token)
                ->first();
        }

        $userDeliveryType = $user->userDeliveryTypes()
            ->select('id', 'delivery_date', 'delivery_type_id', 'time_slot_id')
            ->with([
                'deliveryType' => function ($query) {
                    $query->select('id', 'type_name', 'price', 'currency');
                },
                'timeSlot' => function ($query) {
                    $query->select('id', 'slot_name');
                }
            ])
            ->latest()->first();

        return response()->json(new Response(token: $request->token, data: $userDeliveryType, status: 'success'));
    }

    public function update(UserDeliveryType $delivery_type, UserDeliveryTypeRequest $request): JsonResponse
    {

        $data = $request->except('user_id', 'cart_ids');

        try {
            $delivery_type->update($data);

            $this->updateOrCreateCart($request->get('cart_ids'), $delivery_type->id);

            $delivery_type->load([
                'deliveryType' => function ($query) {
                    $query->select('id', 'type_name', 'price', 'currency');
                },
                'timeSlot' => function ($query) {
                    $query->select('id', 'slot_name');
                },
                'user' => function ($query) {
                    $query->select('id', 'name');
                }
            ]);

        } catch (\Exception $exception) {
            return response()->json(Validation::error($request->token, $exception->getMessage()));
        }

        return response()->json(new Response(token: $request->token, data: $delivery_type, message: "You have successfully updated delivery type"));
    }

    public function destroy(UserDeliveryType $delivery_type): JsonResponse
    {
        $delivery_type->delete();
        return response()->json(new Response(token: request()->token, message: "You have successfully deleted delivery type."));
    }


    private function updateOrCreateCart($cardId, $deliverTypeId)
    {
        $cardIds = [];
        if (is_string($cardId)) {
            $cardIds = explode(',', $cardId);
        }
        Cart::whereIn("id", $cardIds)->update(['user_delivery_type_id' => $deliverTypeId]);
    }
}
