<?php

namespace App\Http\Controllers;

use App\Models\Helper\ControllerHelper;
use App\Models\Helper\Response;
use App\Models\Helper\Validation;
use App\Models\OrderSetting;
use Exception;
use Illuminate\Http\Request;

class OrderSettingsController extends ControllerHelper
{
    public function find(Request $request)
    {
        try {
            $lang = $request->header('language');

            // Must be admin (this route should be behind auth:admin already)
            $admin = $request->user('admin');
            if (!$admin) {
                return response()->json(Validation::errorLang($lang));
            }

            $adminRow = OrderSetting::where('admin_id', $admin->id)->first();
            if ($adminRow) {
                return response()->json(new Response($request->token, $adminRow));
            }

            $globalRow = OrderSetting::whereNull('admin_id')->first();
            if ($globalRow) {
                return response()->json(new Response($request->token, $globalRow));
            }

            // Default if nothing saved yet
            return response()->json(new Response($request->token, [
                'admin_id' => null,
                'auto_cancel_minutes' => 10,
            ]));
        } catch (Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }

    public function action(Request $request)
    {
        try {
            $lang = $request->header('language');

            $admin = $request->user('admin');
            if (!$admin) {
                return response()->json(Validation::errorLang($lang));
            }

            $minutes = (int) $request->auto_cancel_minutes;

            // Simple validation (adjust bounds if you want)
            if ($minutes < 1 || $minutes > 1440) {
                return response()->json(Validation::error(
                    $request->token,
                    'Auto cancel minutes must be between 1 and 1440.'
                ));
            }

            $row = OrderSetting::updateOrCreate(
                ['admin_id' => $admin->id],
                ['auto_cancel_minutes' => $minutes]
            );

            return response()->json(new Response($request->token, $row));
        } catch (Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }
}
