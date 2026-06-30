<?php

namespace Store\KurdistanStore\Services\Search;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Webkul\Product\Repositories\ProductRepository;

class SearchService
{
    public function __construct(protected ProductRepository $productRepository) {}

    public function search(string $query, array $filters = [], int $perPage = 24): LengthAwarePaginator
    {
        $like = '%'.addcslashes(trim($query), '%_').'%';

        return $this->productRepository
            ->with(['product_flats', 'categories', 'inventories', 'images'])
            ->scopeQuery(function ($builder) use ($like, $filters) {
                $builder->whereHas('product_flats', fn ($q) => $q->where('status', 1)->where('visible_individually', 1));

                $builder->where(function ($q) use ($like) {
                    $q->whereHas('product_flats', fn ($inner) => $inner
                        ->where('name', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhere('short_description', 'like', $like)
                    )->orWhere('sku', 'like', $like);
                });

                if (! empty($filters['category_id'])) {
                    $builder->whereHas('categories', fn ($q) => $q->where('id', $filters['category_id']));
                }

                if (! empty($filters['min_price'])) {
                    $builder->whereHas('product_flats', fn ($q) => $q->where('price', '>=', $filters['min_price']));
                }

                if (! empty($filters['max_price'])) {
                    $builder->whereHas('product_flats', fn ($q) => $q->where('price', '<=', $filters['max_price']));
                }

                if (! empty($filters['in_stock'])) {
                    $builder->whereHas('inventories', fn ($q) => $q->where('qty', '>', 0));
                }

                return $builder->orderBy('products.id', 'desc');
            })
            ->paginate($perPage);
    }
}
