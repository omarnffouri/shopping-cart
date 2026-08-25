<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TimeSlotRequest;
use App\Models\DeliveryType;
use App\Models\TimeSlot;
use Illuminate\Http\Request;

class TimeSlotController extends Controller
{
    public function update(TimeSlotRequest $request, TimeSlot $time_slot): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $request->validated();
            $time_slot->update($data);

            return response()->json(['data' => $time_slot, 'message' => 'Time slot updated successfully']);
        } catch (\Exception $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()]);
        }
    }

    public function destroy(TimeSlot $time_slot): \Illuminate\Http\JsonResponse{

        try {
            if($time_slot->userDeliveryTypes->isNotEmpty()) {
                throw new \Exception('You can not delete this time slot it is already used' , 400);
            }

            $time_slot->delete();

            return response()->json(['status' => 'success', 'message' => 'Time slot deleted successfully']);
        } catch (\Exception $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()]);
        }
    }
}
