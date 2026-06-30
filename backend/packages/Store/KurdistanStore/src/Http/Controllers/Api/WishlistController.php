<?php

namespace Store\KurdistanStore\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Store\KurdistanStore\Http\Resources\ProductResource;
use Webkul\Customer\Repositories\WishlistRepository;
use Webkul\Product\Repositories\ProductRepository;

class WishlistController extends Controller
{
    public function __construct(
        protected WishlistRepository $wishlistRepository,
        protected ProductRepository $productRepository,
    ) {}

    public function index(): JsonResponse
    {
        $customer = auth()->user();
        $items = $this->wishlistRepository->findWhere(['customer_id' => $customer->id]);

        $productIds = $items->pluck('product_id')->filter()->unique()->values();

        $products = $this->productRepository
            ->with(['product_flats', 'images', 'categories', 'inventories'])
            ->findWhereIn('id', $productIds->toArray());

        $productMap = collect($products)->keyBy('id');

        $data = $items->map(function ($item) use ($productMap) {
            $product = $productMap->get($item->product_id);

            return [
                'id'         => $item->id,
                'product_id' => $item->product_id,
                'product'    => $product ? (new ProductResource($product))->toArray(request()) : null,
            ];
        })->filter(fn ($i) => $i['product'] !== null)->values();

        return response()->json(['data' => $data]);
    }

    public function store(): JsonResponse
    {
        $customer = auth()->user();
        $productId = (int) request('product_id');

        $this->wishlistRepository->create([
            'customer_id' => $customer->id,
            'product_id' => $productId,
            'channel_id' => core()->getCurrentChannel()->id,
        ]);

        return response()->json(['message' => 'Added to wishlist.'], 201);
    }

    public function destroy(int $productId): JsonResponse
    {
        $customer = auth()->user();

        $this->wishlistRepository->deleteWhere([
            'customer_id' => $customer->id,
            'product_id' => $productId,
        ]);

        return response()->json(['message' => 'Removed from wishlist.']);
    }
}
