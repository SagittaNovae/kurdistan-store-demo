<?php

namespace Store\KurdistanStore\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Store\KurdistanStore\Http\Requests\AddToCartRequest;
use Store\KurdistanStore\Http\Requests\CheckoutRequest;
use Store\KurdistanStore\Http\Resources\CartResource;
use Store\KurdistanStore\Http\Resources\OrderResource;
use Store\KurdistanStore\Services\Checkout\CheckoutService;
use Webkul\Checkout\Facades\Cart;
use Webkul\Product\Repositories\ProductRepository;

class CartController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService,
    ) {}

    public function show(): JsonResponse
    {
        Cart::collectTotals();

        return response()->json(['data' => new CartResource(Cart::getCart())]);
    }

    public function add(AddToCartRequest $request): JsonResponse
    {
        $product = app(ProductRepository::class)->findOrFail($request->product_id);

        try {
            Cart::addProduct($product, ['quantity' => $request->quantity, 'product_id' => $product->id]);
        } catch (\Webkul\Product\Exceptions\InsufficientProductInventoryException) {
            return response()->json([
                'message' => 'This product is out of stock or unavailable in the requested quantity.',
            ], 422);
        } catch (\Throwable $e) {
            Log::error('[Cart] addProduct failed', ['product_id' => $product->id, 'error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Unable to add product to cart. Please try again.',
            ], 500);
        }

        Cart::collectTotals();

        return response()->json([
            'data' => new CartResource(Cart::getCart()),
            'message' => 'Product added to cart.',
        ]);
    }

    public function update(int $itemId): JsonResponse
    {
        $quantity = (int) request('quantity', 1);

        if ($quantity < 1) {
            Cart::removeItem($itemId);
        } else {
            Cart::updateItems(['qty' => [$itemId => $quantity]]);
        }

        Cart::collectTotals();

        return response()->json(['data' => new CartResource(Cart::getCart())]);
    }

    public function remove(int $itemId): JsonResponse
    {
        Cart::removeItem($itemId);
        Cart::collectTotals();

        return response()->json(['data' => new CartResource(Cart::getCart())]);
    }

    public function checkout(CheckoutRequest $request): JsonResponse
    {
        $cart = Cart::getCart();

        $data = $request->validated();

        try {
            $order = $this->checkoutService->placeOrder($data);
        } catch (\RuntimeException $e) {
            // Known business-rule failures (empty cart, invalid payment, etc.)
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('[Checkout] Order placement failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Unable to place order. Please try again.'], 500);
        }

        // Cart::deActivateCart() is called inside placeOrder(); no explicit cleanup needed.

        return response()->json([
            'data' => new OrderResource($order),
            'message' => 'Order placed successfully.',
        ], 201);
    }
}
