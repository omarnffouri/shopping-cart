<?php

namespace App\Models;

use Iyzipay\Model\Address;
use Iyzipay\Model\BasketItem;
use Iyzipay\Model\BasketItemType;
use Iyzipay\Model\Buyer;
use Iyzipay\Model\CheckoutFormInitialize;
use Iyzipay\Model\PaymentGroup;
use Iyzipay\Options;
use Iyzipay\Request\CreateCheckoutFormInitializeRequest;

class IyzicoPayment
{

    public static function options(): Options
    {
        $payment = Payment::first();
        $options = new Options();
        $options->setApiKey($payment->ip_api_key);
        $options->setSecretKey($payment->ip_secret_key);
        $options->setBaseUrl($payment->ip_base_url);
        return $options;
    }


    public static function initIyzico($request, $orderId){

        $order = Order::with('ordered_products.product.product_categories')
            ->with('address')
            ->find($orderId);

        if(!$order){
            return $orderId;
        }

        $requestIyzico = new CreateCheckoutFormInitializeRequest();
        $requestIyzico->setLocale(app()->getLocale());
        $requestIyzico->setConversationId(rand());
        $requestIyzico->setPrice($order->total_amount);
        $requestIyzico->setPaidPrice($order->total_amount);
        $requestIyzico->setCurrency($order->currency);
        $requestIyzico->setBasketId("B67832");
        $requestIyzico->setPaymentGroup(PaymentGroup::PRODUCT);
        $requestIyzico->setCallbackUrl(route('iyzico.callback', ["order_id" => $orderId] ));
        $requestIyzico->setEnabledInstallments(array(2, 3, 6, 9));

        $buyer = new Buyer();
        $buyer->setId($order->address->id);
        $buyer->setName($order->address->name);
        $buyer->setSurname($order->address->name);
        $buyer->setGsmNumber($order->address->phone);
        $buyer->setEmail($order->address->email);
        $buyer->setIdentityNumber(rand());
        $buyer->setRegistrationAddress($order->address->address_1 . $order->address->address_2);
        $buyer->setIp($request->ip());
        $buyer->setCity($order->address->city);
        $buyer->setCountry($order->address->country);
        $buyer->setZipCode($order->address->zip);

        $requestIyzico->setBuyer($buyer);

        $shippingAddress = new Address();
        $shippingAddress->setContactName($order->address->name);
        $shippingAddress->setCity($order->address->city);
        $shippingAddress->setCountry($order->address->country);
        $shippingAddress->setAddress($order->address->address_1 . $order->address->address_2);
        $shippingAddress->setZipCode($order->address->zip);
        $requestIyzico->setShippingAddress($shippingAddress);

        $billingAddress = new Address();
        $billingAddress->setContactName($order->address->name);
        $billingAddress->setCity($order->address->city);
        $billingAddress->setCountry($order->address->country);
        $billingAddress->setAddress($order->address->address_1 . $order->address->address_2);
        $billingAddress->setZipCode($order->address->zip);
        $requestIyzico->setBillingAddress($billingAddress);

        $totalAmount = $order->total_amount;
        $basketItems = [];

        foreach ($order->ordered_products as $op) {
            $BasketItem = new BasketItem();
            $BasketItem->setId($op->product_id);
            $BasketItem->setName($op->product->title);

            if(count($op->product->product_categories) > 0) {
                $BasketItem->setCategory1($op->product->product_categories[0]->id);
            } else {
                $BasketItem->setCategory1("No category");
            }

            $price = ($op->selling * ($op->quantity - $op->bundle_offer)) + ($op->tax_price * $op->quantity) + $op->shipping_price;

            $totalAmount -= $price;

            $BasketItem->setItemType(BasketItemType::PHYSICAL);
            $BasketItem->setPrice($price);

            $basketItems[] = $BasketItem;
        }

        if($totalAmount > 0){
            $BasketItem = new BasketItem();
            $BasketItem->setId("Product");
            $BasketItem->setName("Product");
            $BasketItem->setCategory1("Product");

            $BasketItem->setItemType(BasketItemType::PHYSICAL);
            $BasketItem->setPrice($totalAmount);

            $basketItems[] = $BasketItem;
        }

        $requestIyzico->setBasketItems($basketItems);

       //$paymentForm = $checkoutFormInitialize->getCheckoutFormContent();

        $checkoutFormInitialize = CheckoutFormInitialize::create($requestIyzico, IyzicoPayment::options());
        // $paymentForm = $checkoutFormInitialize->getCheckoutFormContent();

        return [
            "paymentPageUrl" => $checkoutFormInitialize->getPaymentPageUrl(),
            "checkoutFormContent" => $checkoutFormInitialize->getCheckoutFormContent(),
            "rawResult" => $checkoutFormInitialize->getRawResult(),
            "status" => $checkoutFormInitialize->getStatus(),
            "errorMessage" => $checkoutFormInitialize->getErrorMessage()
        ];
    }

}
