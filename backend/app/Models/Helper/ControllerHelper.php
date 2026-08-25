<?php

namespace App\Models\Helper;

use App\Http\Controllers\Controller;
use App\Models\UpdatedInventory;
use Illuminate\Support\Facades\Auth;

class ControllerHelper extends Controller
{
    protected $user;
    protected $isVendor = false;
    protected $isSuperAdmin = false;

    public function __construct()
    {
        $this->middleware(function ($request, $next){
            $this->user = Auth::guard('admin')->user();
            if(!is_null($this->user)){
                foreach ($this->user->roles->pluck('name') as $i){
                    if($i == 'vendor') {
                        $this->isVendor = true;
                        break;
                    } else if($i == 'superadmin') {
                        $this->isSuperAdmin = true;
                        break;
                    }
                }
            }

            return $next($request);
        });
    }

    /**
     * Restock inventory when an order is canceled
     *
     * @param $orderProducts
     * @return void
     */
    protected function reStockInventory($orderProducts): void
    {
        foreach ($orderProducts as $order_product) {
            $inventory = UpdatedInventory::where('product_id', $order_product->product_id)->first();
            if ($inventory) {
                $inventory->quantity += $order_product->quantity;
                $inventory->save();
            }
        }
    }
}
