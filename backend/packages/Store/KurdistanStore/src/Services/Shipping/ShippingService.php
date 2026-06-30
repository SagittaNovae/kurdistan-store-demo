<?php

namespace Store\KurdistanStore\Services\Shipping;

use Illuminate\Database\Eloquent\Collection;
use Store\KurdistanStore\Models\DeliveryZone;

class ShippingService
{
    public function quote(string $governorate, ?string $district = null): array
    {
        $zone = null;

        if ($district) {
            $zone = DeliveryZone::where('governorate', $governorate)
                ->where('district', $district)
                ->where('is_active', true)
                ->first();
        }

        if (! $zone) {
            $zone = DeliveryZone::where('governorate', $governorate)
                ->whereNull('district')
                ->where('is_active', true)
                ->first();
        }

        if (! $zone) {
            return [
                'available' => false,
                'governorate' => $governorate,
                'district' => $district,
                'message' => 'Delivery is not available for this location.',
            ];
        }

        return [
            'available' => true,
            'governorate' => $zone->governorate,
            'district' => $district ?? $zone->district,
            'rate' => (float) $zone->flat_rate,
            'rate_formatted' => core()->formatPrice($zone->flat_rate),
            'estimated_days' => $zone->estimated_days,
        ];
    }

    public function zones(): Collection
    {
        return DeliveryZone::where('is_active', true)
            ->orderBy('governorate')
            ->orderByRaw('district IS NULL DESC')
            ->orderBy('district')
            ->get();
    }

    public function groupedZones(): array
    {
        return $this->zones()
            ->groupBy('governorate')
            ->map(fn ($zones) => $zones->map(fn ($z) => [
                'district' => $z->district,
                'rate' => (float) $z->flat_rate,
                'rate_formatted' => core()->formatPrice($z->flat_rate),
                'estimated_days' => $z->estimated_days,
            ])->values())
            ->toArray();
    }
}
