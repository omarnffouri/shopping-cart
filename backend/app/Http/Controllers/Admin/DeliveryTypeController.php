<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeliveryTypeRequest;
use App\Models\DeliveryType;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DeliveryTypeController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
       $types =  DeliveryType::query()
           ->selectRaw('MAX(id) as id , country  , state , MIN(type_name) as type_name, is_default')
           ->when($request->query('country') || $request->query('state'), function ($query) use ($request) {
               $query->where(function ($query) use ($request) {
                   $query->where('country' , $request->query('country'))
                       ->orWhere('state' , $request->query('state'));
               });
           })
           ->orderBy('id' , 'DESC')
           ->groupBy('country' ,'state')
           ->paginate(20);


       return response()->json(['data' => $types]);
    }

    public function getDeliveryTypeByState($state): \Illuminate\Http\JsonResponse
    {
        $types = DeliveryType::query()
            ->select(['id','type_name','type_code',
                'price','currency','country',
                'state','description','min_hours_advance',
                'available_for_today','cutoff_time',
                'display_order','is_active','is_default',
            ])
            ->with(['timeSlots' => function ($q) {
                $q->select(['id','delivery_type_id', 'slot_name','start_time',
                    'end_time','available_if_before','display_order','is_active',
                ])->orderBy('display_order');
            }])
            ->where('state', $state)
            ->orderBy('display_order')
            ->get();

        $results = $types->transform(function ($item) {
            return [
                ...collect($item)->toArray(),
                'time_slots' => $item->timeSlots?->map(function ($slot) {
                    return collect($slot)->toArray();
                })
            ];
        })->values();

        return response()->json(['data' => $results]);
    }

    public function store(DeliveryTypeRequest $request): \Illuminate\Http\JsonResponse {
        $validated = $request->validated();

      DB::transaction(function () use ($validated) {
           foreach ($validated['states'] as $value) {

               $delivery_type = DeliveryType::query()->create([
                   ...Arr::except($validated, 'time_slots') ,
                   'state' => $value
               ]);

               $delivery_type->timeSlots()->createMany($validated['time_slots']);
           }
        });

        return response()->json(['data' => 'Delivery Type created successfully']);
    }

    public function edit(DeliveryType $admin_delivery_type): \Illuminate\Http\JsonResponse {
        $admin_delivery_type->load('timeSlots');
        return response()->json($admin_delivery_type);
    }

    public function update(DeliveryType $admin_delivery_type , DeliveryTypeRequest $request): \Illuminate\Http\JsonResponse {

        try {
        $validated = $request->validated();

        $exist = DeliveryType::query()
            ->where('state' , $admin_delivery_type->state)
            ->whereNotNull('state')
            ->where('is_default' , true)
            ->select('id' , 'type_name')
            ->first();

        DB::transaction(function () use ($validated , $admin_delivery_type) {
            $admin_delivery_type->update(Arr::except($validated , 'time_slots'));

            DB::table('time_slots')->upsert(
                collect($validated['time_slots'])->map(function ($slot) use ($admin_delivery_type) {
                    $slot['delivery_type_id'] = $admin_delivery_type->id;
                    return $slot;
                })->toArray(),
                ['id'],
                ['slot_name', 'start_time', 'end_time',
                    'available_if_before', 'display_order', 'is_active']
            );

            return $admin_delivery_type;
        });

         $admin_delivery_type->load('timeSlots');

        return response()->json(['data' => $admin_delivery_type]);

        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage() , 'code' => $exception->getCode()]);
        }

    }

    public function destroy(DeliveryType $admin_delivery_type): \Illuminate\Http\JsonResponse{

        try {
            $admin_delivery_type->load('userDeliveryTypes');

            if ($admin_delivery_type->userDeliveryTypes->isNotEmpty()) {
                throw new \Exception('You can not delete this delivery type it is already used' , 400);
            }

            $admin_delivery_type->delete();

            return response()->json(['message' => "Delivery Type Deleted Successfully"]);
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage() , 'status' => $exception->getCode()]);
        }
    }
}
