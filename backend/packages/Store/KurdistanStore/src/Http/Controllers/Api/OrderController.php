<?php

namespace Store\KurdistanStore\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Store\KurdistanStore\Http\Resources\OrderResource;
use Webkul\Sales\Repositories\OrderRepository;

class OrderController extends Controller
{
    public function __construct(protected OrderRepository $orderRepository) {}

    public function index(): JsonResponse
    {
        $customer = auth()->user();

        $orders = $this->orderRepository
            ->findWhere(['customer_id' => $customer->id])
            ->sortByDesc('created_at')
            ->values();

        return response()->json(['data' => OrderResource::collection($orders)]);
    }

    public function show(int $id): JsonResponse
    {
        $customer = auth()->user();
        $order    = $this->orderRepository->findOrFail($id);

        // Return 404 rather than 403 so the existence of another customer's
        // order is not confirmed to an attacker enumerating IDs.
        if ((int) $order->customer_id !== (int) $customer->id) {
            abort(404);
        }

        // Eager-load every relation the resource needs to avoid N+1 queries.
        $order->loadMissing(['items.product', 'payment', 'shipments', 'addresses']);

        return response()->json(['data' => new OrderResource($order)]);
    }
}
