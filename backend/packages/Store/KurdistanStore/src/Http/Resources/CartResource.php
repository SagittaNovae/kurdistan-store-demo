<?php

namespace Store\KurdistanStore\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (! $this->resource) {
            return [
                'id' => null,
                'items_count' => 0,
                'items_qty' => 0,
                'sub_total' => 0.0,
                'sub_total_formatted' => core()->formatPrice(0),
                'shipping_amount' => 0.0,
                'grand_total' => 0.0,
                'grand_total_formatted' => core()->formatPrice(0),
                'items' => [],
                'coupon_code' => null,
            ];
        }

        return [
            'id' => $this->id,
            'items_count' => $this->items_count ?? count($this->items ?? []),
            'items_qty' => $this->items_qty ?? 0,
            'sub_total' => (float) ($this->sub_total ?? 0),
            'sub_total_formatted' => core()->formatPrice($this->sub_total ?? 0),
            'shipping_amount' => (float) ($this->selected_shipping_rate?->price ?? config('kurdistan-store.shipping.flat_rate', 5)),
            'grand_total' => (float) ($this->grand_total ?? 0),
            'grand_total_formatted' => core()->formatPrice($this->grand_total ?? 0),
            'items' => CartItemResource::collection($this->items ?? []),
            'coupon_code' => $this->coupon_code ?? null,
        ];
    }
}
