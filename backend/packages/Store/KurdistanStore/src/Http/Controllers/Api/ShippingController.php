<?php

namespace Store\KurdistanStore\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Store\KurdistanStore\Services\Shipping\ShippingService;

class ShippingController extends Controller
{
    public function __construct(protected ShippingService $shippingService) {}

    public function quote(): JsonResponse
    {
        $data = request()->validate([
            'governorate' => ['required', 'string', 'max:64'],
            'district' => ['nullable', 'string', 'max:64'],
        ]);

        return response()->json([
            'data' => $this->shippingService->quote(
                $data['governorate'],
                $data['district'] ?? null
            ),
        ]);
    }

    public function zones(): JsonResponse
    {
        $zones = Cache::tags(['kurdistan.shipping'])->remember('shipping.zones', 3600, function () {
            return $this->shippingService->groupedZones();
        });

        return response()->json(['data' => $zones]);
    }
}
