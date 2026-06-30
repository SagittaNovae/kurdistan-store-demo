<?php

namespace Store\KurdistanStore\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Store\KurdistanStore\Http\Resources\AddressResource;
use Store\KurdistanStore\Models\CustomerDeliveryAddress;

class AddressController extends Controller
{
    private const MAX_ADDRESSES = 10;
    private const IRAQ_LAT_MIN = 29.06;
    private const IRAQ_LAT_MAX = 37.38;
    private const IRAQ_LNG_MIN = 38.79;
    private const IRAQ_LNG_MAX = 48.76;

    public function index(): JsonResponse
    {
        $addresses = CustomerDeliveryAddress::where('customer_id', auth()->id())
            ->orderByDesc('is_default')
            ->orderBy('created_at')
            ->get();

        return response()->json(['data' => AddressResource::collection($addresses)]);
    }

    public function store(Request $request): JsonResponse
    {
        $customerId = auth()->id();

        $count = CustomerDeliveryAddress::where('customer_id', $customerId)->count();
        if ($count >= self::MAX_ADDRESSES) {
            return response()->json(['message' => 'Maximum of ' . self::MAX_ADDRESSES . ' saved addresses allowed.'], 422);
        }

        $validated = $this->validateAddress($request);
        $validated['customer_id'] = $customerId;

        if ($validated['is_default'] ?? false) {
            CustomerDeliveryAddress::where('customer_id', $customerId)->update(['is_default' => false]);
        }

        // First address is automatically the default
        if ($count === 0) {
            $validated['is_default'] = true;
        }

        $address = CustomerDeliveryAddress::create($validated);

        return response()->json(['data' => new AddressResource($address)], 201);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $address = $this->findOwned($id);

        $validated = $this->validateAddress($request);

        if ($validated['is_default'] ?? false) {
            CustomerDeliveryAddress::where('customer_id', auth()->id())
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);
        }

        $address->update($validated);

        return response()->json(['data' => new AddressResource($address->fresh())]);
    }

    public function destroy(int $id): JsonResponse
    {
        $address = $this->findOwned($id);
        $wasDefault = $address->is_default;
        $customerId = auth()->id();

        $address->delete();

        // Auto-promote the oldest remaining address to default
        if ($wasDefault) {
            CustomerDeliveryAddress::where('customer_id', $customerId)
                ->orderBy('created_at')
                ->first()?->update(['is_default' => true]);
        }

        return response()->json(['message' => 'Address deleted.']);
    }

    public function setDefault(int $id): JsonResponse
    {
        $address = $this->findOwned($id);
        $customerId = auth()->id();

        CustomerDeliveryAddress::where('customer_id', $customerId)->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return response()->json(['data' => new AddressResource($address->fresh())]);
    }

    private function findOwned(int $id): CustomerDeliveryAddress
    {
        $address = CustomerDeliveryAddress::where('id', $id)
            ->where('customer_id', auth()->id())
            ->firstOrFail();

        return $address;
    }

    private function validateAddress(Request $request): array
    {
        $locations    = config('kurdistan-store.locations', []);
        $governorates = array_keys($locations);

        return $request->validate([
            'label'        => ['sometimes', 'string', Rule::in(['Home', 'Work', 'Other'])],
            'nickname'     => ['nullable', 'string', 'max:100'],
            'address_text' => ['nullable', 'string', 'max:500'],
            'governorate'  => ['required', 'string', Rule::in($governorates)],
            'city'         => [
                'required',
                'string',
                'max:100',
                function (string $attribute, mixed $value, \Closure $fail) use ($locations) {
                    $gov = request()->input('governorate');
                    if ($gov && isset($locations[$gov]) && ! in_array($value, $locations[$gov], true)) {
                        $fail('The selected city is not valid for the chosen governorate.');
                    }
                },
            ],
            'address_line' => ['required', 'string', 'max:500'],
            'latitude'     => [
                'required', 'numeric',
                'min:' . self::IRAQ_LAT_MIN, 'max:' . self::IRAQ_LAT_MAX,
            ],
            'longitude'    => [
                'required', 'numeric',
                'min:' . self::IRAQ_LNG_MIN, 'max:' . self::IRAQ_LNG_MAX,
            ],
            'is_default'   => ['sometimes', 'boolean'],
        ]);
    }
}
