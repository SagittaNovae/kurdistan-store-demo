<?php

namespace Store\KurdistanStore\Services\Checkout;

use Store\KurdistanStore\Services\Payment\Gateways\CashOnDeliveryGateway;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Models\OrderComment;
use Webkul\Sales\Repositories\OrderRepository;

class CheckoutService
{
    public function __construct(
        protected OrderRepository $orderRepository,
        protected CashOnDeliveryGateway $codGateway
    ) {}

    public function placeOrder(array $data): object
    {
        $cart = Cart::getCart();

        if (! $cart || $cart->items->isEmpty()) {
            throw new \RuntimeException('Cart is empty.');
        }

        $user = auth('customer')->user();
        Cart::saveAddresses([
            'billing' => [
                'first_name'       => $data['first_name'],
                'last_name'        => $data['last_name'],
                'email'            => $user?->email ?? ($data['phone'].'@noreply.local'),
                'phone'            => $data['phone'],
                'address'          => [$data['address']],
                'city'             => $data['city'],
                'state'            => $data['governorate'],
                'country'          => 'IQ',
                'postcode'         => '00000',
                'use_for_shipping' => true,
            ],
        ]);

        Cart::saveShippingMethod('flatrate_flatrate');
        Cart::savePaymentMethod(['method' => $data['payment_method']]);
        Cart::collectTotals();

        // collectTotals() calls refreshCart() internally, which replaces the Cart
        // singleton's $this->cart with a new DB instance. Re-fetch so the local
        // $cart variable reflects the updated grand_total (including shipping).
        $cart = Cart::getCart();

        // savePaymentMethod() writes to DB but never sets the relation on the in-memory model.
        $cart->load('payment');

        $orderData = (new \Webkul\Sales\Transformers\OrderResource($cart))->jsonSerialize();
        $order = $this->orderRepository->create($orderData);

        // Persist delivery coordinates on the shipping address.
        // Uses DB::table() directly to bypass model fillable restrictions.
        if (! empty($data['delivery_latitude']) && ! empty($data['delivery_longitude'])) {
            \Illuminate\Support\Facades\DB::table('addresses')
                ->where('order_id', $order->id)
                ->update([
                    'delivery_latitude'  => (float) $data['delivery_latitude'],
                    'delivery_longitude' => (float) $data['delivery_longitude'],
                ]);

            $lat  = $data['delivery_latitude'];
            $lng  = $data['delivery_longitude'];
            $addr = ! empty($data['delivery_address_text']) ? $data['delivery_address_text'] : null;

            $commentLines = [];
            if ($addr) {
                $commentLines[] = 'Delivery Location: ' . $addr;
            }
            $commentLines[] = sprintf('Coordinates: %.7f° N, %.7f° E', $lat, $lng);
            $commentLines[] = sprintf('Maps: https://www.google.com/maps?q=%s,%s', $lat, $lng);

            OrderComment::create([
                'order_id'          => $order->id,
                'comment'           => implode("\n", $commentLines),
                'customer_notified' => false,
            ]);
        }

        if (! empty($data['notes'])) {
            OrderComment::create([
                'order_id'          => $order->id,
                'comment'           => 'Customer note: '.$data['notes'],
                'customer_notified' => false,
            ]);
        }

        $paymentResult = $this->codGateway->initiate([
            'order_id'    => $order->id,
            'customer_id' => $order->customer_id,
            'amount'      => $order->grand_total,
            'currency'    => $order->order_currency_code,
        ]);

        Cart::deActivateCart();

        $order->payment_result = $paymentResult;

        return $order;
    }
}
