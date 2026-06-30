<?php

namespace Store\KurdistanStore\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Product\Repositories\ProductReviewRepository;
use Webkul\Sales\Repositories\OrderRepository;

class ReviewController extends Controller
{
    public function __construct(
        protected ProductReviewRepository $reviewRepository,
        protected ProductRepository $productRepository,
        protected OrderRepository $orderRepository,
    ) {}

    public function index(int $productId): JsonResponse
    {
        $reviews = $this->reviewRepository->findWhere([
            'product_id' => $productId,
            'status' => 'approved',
        ]);

        $data = $reviews->map(fn ($r) => [
            'id' => $r->id,
            'title' => $r->title,
            'comment' => $r->comment,
            'rating' => (int) $r->rating,
            'name' => $r->name,
            'created_at' => $r->created_at?->toDateString(),
        ])->values();

        return response()->json(['data' => $data]);
    }

    public function store(int $productId): JsonResponse
    {
        $customer = auth()->user();

        request()->validate([
            'title'   => ['required', 'string', 'max:255'],
            'comment' => ['required', 'string', 'max:2000'],
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $this->productRepository->findOrFail($productId);

        // One review per customer per product
        $existing = $this->reviewRepository->findOneWhere([
            'product_id'  => $productId,
            'customer_id' => $customer->id,
        ]);

        if ($existing) {
            return response()->json(['message' => 'You have already reviewed this product.'], 422);
        }

        $this->reviewRepository->create([
            'product_id'  => $productId,
            'customer_id' => $customer->id,
            'name'        => $customer->name,
            'title'       => request('title'),
            'comment'     => request('comment'),
            'rating'      => request('rating'),
            'status'      => 'pending',
        ]);

        return response()->json(['message' => 'Review submitted and awaiting moderation.'], 201);
    }
}
