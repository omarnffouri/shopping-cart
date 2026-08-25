<?php

namespace App\Http\Controllers;

use App\Models\Cancellation;
use App\Models\Helper\ControllerHelper;
use App\Models\Helper\Response;
use App\Models\Helper\Validation;
use App\Models\Order;
use App\Models\OrderedProduct;
use App\Models\RatingReview;
use App\Models\ReviewImage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class CancellationsController extends ControllerHelper
{

    public function refund(Request $request, $id)
    {

        try {

            $lang = $request->header('language');
            $cancellation = Cancellation::find($id);

            if (is_null($cancellation)) {
                return response()->json(Validation::nothing_found(201, null, 'form', $lang));
            }

            $order = Order::find($cancellation->order_id);

            if ($order->payment_done != Config::get('constants.status.PUBLIC')) {
                return response()->json(Validation::error($request->token,
                    __('lang.unpaid_order', [], $lang)));
            }

            if ($order->order_method == Config::get('constants.paymentMethod.CASH_ON_DELIVERY')) {
                return response()->json(Validation::error($request->token,
                    __('lang.unable_refund', [], $lang)));
            }

            $hasNotRefundableProduct = OrderedProduct::join('products as p', function ($join) use ($cancellation) {
                $join->on('p.id', '=', 'ordered_products.product_id');
                $join->where('ordered_products.order_id', $cancellation->order_id);
                $join->where('p.refundable', '!=', Config::get('constants.status.PUBLIC'));
            })
                ->first();

            if (!is_null($hasNotRefundableProduct)) {
                return response()->json(Validation::error($request->token,
                    __('lang.not_refund', [], $lang)));
            }

            Cancellation::where('id', $id)->update(['refunded' => true]);

            $cancellation['refunded'] = 1;

            return Validation::success(
                $request,
                __('lang.refunded', [], $lang),
                $cancellation
            );


        } catch (Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }

    public function find(Request $request, $orderId)
    {
        try {

            $lang = $request->header('language');
            $cancellation = Cancellation::where('order_id', $orderId)->get()->first();

            if (is_null($cancellation)) {
                return response()->json(Validation::nothing_found(201, null, 'form', $lang));
            }

            return response()->json(new Response($request->token, $cancellation));


        } catch (Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }

    public function findCancellation(Request $request, $orderId)
    {
        try {
            $lang = $request->header('language');
            $query = Cancellation::where('order_id', $orderId);
            if ($request->user('user')) {
                $query = $query->where('user_id', $request->user('user')->id);
            } else if ($request->user_token) {
                $query = $query->where('user_token', $request->user_token);
            } else {
                return response()->json(Validation::errorLang($lang));
            }
            $cancellation = $query->first();
            if (is_null($cancellation)) {
                return response()->json(Validation::nothing_found(201, null, 'form', $lang));
            }
            return response()->json(new Response($request->token, $cancellation));

        } catch (Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }

    public function cancelOrder(Request $request)
    {
        try {
            $lang = $request->header('language');

            $validate = Validation::cancelled($request);
            if ($validate) return response()->json($validate);

            $admin = $request->user('admin');
            if (!$admin) return response()->json(Validation::errorLang($lang));

            $order = Order::find($request->order_id);
            if (!$order) return response()->json(Validation::nothingFoundLang($lang));

            if ($order->cancelled) {
                $cancellation = Cancellation::where('order_id', $order->id)->first();
                $cancellation?->update([
                    'title' => $request->title,
                    'message' => $request->message,
                    'admin_id' => $admin->id
                ]);
                $order->load('cancellation');
                return Validation::success($request, __('Order already cancelled. Details updated only.', [], $lang), [
                    'result' => ['cancelled' => true, 'order_id' => $order->id,'cancellation' => $order->cancellation,]
                ]);
            }

            if ($order->status != Config::get('constants.orderStatus.PENDING')) {
                return response()->json(Validation::error($request->token, __('lang.cancel_order', [], $lang)));
            }

            // Cancel the order
            $order->update(['cancelled' => true]);

            // Create a cancellation record
            $cancellation = Cancellation::updateOrCreate(
                ['order_id' => $order->id, 'admin_id' => $admin->id],
                ['title' => $request->title, 'message' => $request->message]
            );
            $order->setRelation('cancellation', $cancellation);
            // Restock inventory
            $this->reStockInventory($order->ordered_p);

            // Delete existing rating/reviews if any
            ReviewImage::leftJoin('rating_reviews', 'review_images.rating_review_id', '=', 'rating_reviews.id')
                ->where('rating_reviews.order_id', $order->id)->delete();
            RatingReview::where('order_id', $order->id)->delete();

            return Validation::success($request, __('lang.cancelled', [], $lang), [
                'result' => ['cancelled' => true, 'order_id' => $order->id,'cancellation' => $order->cancellation,]
            ]);

        } catch (Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }
}
