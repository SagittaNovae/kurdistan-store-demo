<?php

namespace Store\KurdistanStore\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $product = $this->product;

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'sku' => $product->sku ?? null,
            'name' => $this->name,
            'price' => (float) $this->price,
            'price_formatted' => core()->formatPrice($this->price),
            'quantity' => (int) $this->quantity,
            'total' => (float) $this->total,
            'image' => $product?->images?->first()?->url,
            'stock' => (int) ($product?->inventories?->sum('qty') ?? 0),
        ];
    }
}
