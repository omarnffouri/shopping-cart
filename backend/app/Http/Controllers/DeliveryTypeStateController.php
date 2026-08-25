<?php

namespace App\Http\Controllers;

use App\Models\DeliveryType;
use App\Traits\CountryAndStateTrait;
use App\Models\Helper\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryTypeStateController extends Controller
{
    use CountryAndStateTrait;

    public function __invoke(Request $request): JsonResponse
    {

        $countryData = $this->getCountryByCountryCode();

        $deliveryTypes = DeliveryType::query()
            ->select('id' , 'state')
            ->where('is_active', true)
            ->groupBy('state')
            ->orderBy('state')
            ->get();

        $deliveryTypes =  $deliveryTypes->map(function ($item) use ($countryData) {
            $state = $countryData['states'][$item->state] ?? "";

            if (empty($state['name'])) {
                return null;
            }

            return [
                'code' => $item['state'],
                'name' => $state['name'],
            ];
        })->filter()
        ->values();

        return response()->json(new Response($request->token, $deliveryTypes));
    }
}
