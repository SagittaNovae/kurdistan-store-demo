<?php

namespace Store\KurdistanStore\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                       => $this->id,
            'increment_id'             => $this->increment_id,
            'status'                   => $this->status,
            'created_at'               => $this->created_at?->toIso8601String(),

            // Totals
            'sub_total'                => (float) $this->sub_total,
            'sub_total_formatted'      => core()->formatPrice($this->sub_total),
            'shipping_amount'          => (float) $this->shipping_amount,
            'shipping_amount_formatted'=> core()->formatPrice($this->shipping_amount),
            'discount_amount'          => (float) $this->discount_amount,
            'discount_amount_formatted'=> core()->formatPrice($this->discount_amount),
            'tax_amount'               => (float) $this->tax_amount,
            'tax_amount_formatted'     => core()->formatPrice($this->tax_amount),
            'grand_total'              => (float) $this->grand_total,
            'grand_total_formatted'    => core()->formatPrice($this->grand_total),

            // Shipping & payment
            'shipping_method'          => $this->shipping_method,
            'shipping_title'           => $this->shipping_title,
            'payment_method'           => $this->payment?->method,
            'payment_method_title'     => $this->payment?->method_title,

            // Items
            'items' => $this->items?->map(fn ($item) => [
                'name'            => $item->name,
                'sku'             => $item->sku,
                'qty'             => $item->qty_ordered,
                'price'           => (float) $item->price,
                'price_formatted' => core()->formatPrice($item->price),
                'total'           => (float) $item->total,
                'total_formatted' => core()->formatPrice($item->total),
                'image'           => $this->resolveItemImage($item),
            ]),

            // Addresses
            'shipping_address' => $this->formatAddress($this->shipping_address),
            'billing_address'  => $this->formatAddress($this->billing_address),

            // Shipments / tracking
            'shipments' => $this->shipments?->map(fn ($shipment) => [
                'carrier_title' => $shipment->carrier_title,
                'track_number'  => $shipment->track_number,
                'created_at'    => $shipment->created_at?->toIso8601String(),
            ]),
        ];
    }

    private function formatAddress($address): ?array
    {
        if (! $address) {
            return null;
        }

        return [
            'name'               => trim(($address->first_name ?? '').' '.($address->last_name ?? '')),
            'phone'              => $address->phone,
            'city'               => $address->city,
            'address'            => $address->address,
            'delivery_latitude'  => isset($address->delivery_latitude)  ? (float) $address->delivery_latitude  : null,
            'delivery_longitude' => isset($address->delivery_longitude) ? (float) $address->delivery_longitude : null,
            'maps_link'          => (! empty($address->delivery_latitude) && ! empty($address->delivery_longitude))
                ? 'https://www.google.com/maps?q='.$address->delivery_latitude.','.$address->delivery_longitude
                : null,
        ];
    }

    private function resolveItemImage($item): ?string
    {
        try {
            $product = $item->product;
            if (! $product) {
                return null;
            }

            $image = \Webkul\Product\Facades\ProductImage::getProductBaseImage($product);

            return $image['small_image_url'] ?? null;
        } catch (\Throwable) {
            return null;
        }
    }
}
