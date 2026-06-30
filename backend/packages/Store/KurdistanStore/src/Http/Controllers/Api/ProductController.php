<?php

namespace Store\KurdistanStore\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Store\KurdistanStore\Http\Resources\ProductResource;
use Store\KurdistanStore\Services\Search\SearchService;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Repositories\ProductRepository;

class ProductController extends Controller
{
    public function __construct(
        protected ProductRepository $productRepository,
        protected CategoryRepository $categoryRepository,
        protected SearchService $searchService
    ) {}

    public function index(): JsonResponse
    {
        $query = request()->only(['category_id', 'search', 'min_price', 'max_price', 'in_stock', 'sort', 'per_page']);
        $perPage = min((int) ($query['per_page'] ?? 24), 100);

        $cacheKey = 'products.'.md5(json_encode($query + ['page' => request()->get('page', 1)]));

        if (! empty($query['search'])) {
            $data = Cache::tags(['kurdistan.products'])->remember($cacheKey, 300, function () use ($query, $perPage) {
                $products = $this->searchService->search($query['search'], $query, $perPage);

                return [
                    'data' => ProductResource::collection($products)->toArray(request()),
                    'meta' => [
                        'current_page' => $products->currentPage(),
                        'last_page' => $products->lastPage(),
                        'total' => $products->total(),
                    ],
                ];
            });

            return response()->json($data);
        }

        $data = Cache::tags(['kurdistan.products'])->remember($cacheKey, 300, function () use ($query, $perPage) {
            $products = $this->productRepository
                ->with(['product_flats', 'categories', 'inventories', 'images'])
                ->scopeQuery(function ($builder) use ($query) {
                    $builder->whereHas('product_flats', fn ($q) => $q->where('status', 1)->where('visible_individually', 1));

                    if (! empty($query['category_id'])) {
                        $builder->whereHas('categories', fn ($q) => $q->where('id', $query['category_id']));
                    }

                    if (! empty($query['min_price'])) {
                        $builder->whereHas('product_flats', fn ($q) => $q->where('price', '>=', $query['min_price']));
                    }

                    if (! empty($query['max_price'])) {
                        $builder->whereHas('product_flats', fn ($q) => $q->where('price', '<=', $query['max_price']));
                    }

                    if (! empty($query['in_stock'])) {
                        $builder->whereHas('inventories', fn ($q) => $q->where('qty', '>', 0));
                    }

                    match ($query['sort'] ?? 'newest') {
                        'price_asc' => $builder->orderByRaw(
                            'COALESCE((SELECT MIN(price) FROM product_flat WHERE product_id = products.id AND locale = "en"), 999999999) ASC'
                        ),
                        'price_desc' => $builder->orderByRaw(
                            'COALESCE((SELECT MAX(price) FROM product_flat WHERE product_id = products.id AND locale = "en"), 0) DESC'
                        ),
                        'oldest' => $builder->orderBy('products.id', 'asc'),
                        default => $builder->orderBy('products.id', 'desc'),
                    };

                    return $builder;
                })
                ->paginate($perPage);

            return [
                'data' => ProductResource::collection($products)->toArray(request()),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'total' => $products->total(),
                ],
            ];
        });

        return response()->json($data);
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->productRepository
            ->with(['product_flats', 'categories', 'inventories', 'images', 'attribute_values', 'variants.product_flats', 'variants.inventories'])
            ->findOrFail($id);

        return response()->json([
            'data' => new ProductResource($product),
        ]);
    }

    public function showBySlug(string $slug): JsonResponse
    {
        $product = $this->productRepository
            ->with(['product_flats', 'categories', 'inventories', 'images'])
            ->findBySlug($slug);

        if (! $product) {
            abort(404);
        }

        return response()->json(['data' => new ProductResource($product)]);
    }
}
