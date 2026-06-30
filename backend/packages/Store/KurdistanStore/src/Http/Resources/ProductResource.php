<?php

namespace Store\KurdistanStore\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $flat = $this->product_flats?->first(fn ($f) => ! empty($f->name))
            ?? $this->product_flats?->first();
        $category = $this->categories?->first();
        $stock = (int) ($this->inventories?->sum('qty') ?? 0);
        $image = $this->images?->first();

        $specialPrice = $flat && ! empty($flat->special_price) ? (float) $flat->special_price : null;

        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'type' => $this->type,
            'name' => $flat->name ?? $this->sku,
            'slug' => $flat->url_key ?? null,
            'price' => (float) ($flat->price ?? 0),
            'price_formatted' => core()->formatPrice($flat->price ?? 0),
            'special_price' => $specialPrice,
            'special_price_formatted' => $specialPrice ? core()->formatPrice($specialPrice) : null,
            'description' => strip_tags($flat->description ?? ''),
            'short_description' => strip_tags($flat->short_description ?? ''),
            'category' => $category?->name ?? 'General',
            'category_id' => $category?->id,
            'stock' => $stock,
            'in_stock' => $stock > 0,
            'image' => $image ? $image->url : null,
            'images' => $this->whenLoaded('images', fn () => $this->images->pluck('url')),
            'variants' => $this->when(
                $this->relationLoaded('variants'),
                fn () => $this->variants->map(function ($variant) {
                    $vFlat = $variant->product_flats?->first(fn ($f) => ! empty($f->name))
                        ?? $variant->product_flats?->first();
                    $vStock = (int) ($variant->inventories?->sum('qty') ?? 0);

                    return [
                        'id' => $variant->id,
                        'sku' => $variant->sku,
                        'name' => $vFlat?->name ?? $variant->sku,
                        'price' => (float) ($vFlat?->price ?? 0),
                        'price_formatted' => core()->formatPrice($vFlat?->price ?? 0),
                        'stock' => $vStock,
                        'in_stock' => $vStock > 0,
                    ];
                })->values(),
            ),
            'meta' => [
                'title' => $flat->meta_title ?? $flat->name,
                'description' => $flat->meta_description ?? strip_tags($flat->short_description ?? ''),
            ],
        ];
    }
}
