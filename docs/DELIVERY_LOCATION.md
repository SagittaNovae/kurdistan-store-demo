# Delivery Location — Integration Reference

Post-Phase-7 feature. Do not implement until Phase 7 exit criteria are met.
This document records exactly what to build and where, so no existing code needs restructuring.

---

## Architecture decision: separate table

Store delivery coordinates in a new `order_delivery_locations` table inside the KurdistanStore
package — **not** in `addresses.additional` (JSON, unindexable, invisible to admin DataGrid)
and **not** as new columns on `addresses` (Bagisto core table; risky across upgrades).

The `order_delivery_locations` table has a `UNIQUE` FK on `order_id`, so every `Order` gets at
most one delivery pin. Courier integrations query this table directly by `order_id`.

---

## Why current code is already compatible

Audit results — nothing in the existing checkout pipeline assumes location data is text-only:

- `CheckoutRequest::validated()` returns only declared rules. Adding nullable fields is purely
  additive; no existing callers are affected.
- `CheckoutService::placeOrder(array $data)` reads specific keys from `$data`. Unknown keys are
  ignored. A single `DeliveryLocation::create()` call after `$this->orderRepository->create()`
  is the only insertion point needed.
- `KurdistanStore\Http\Resources\OrderResource` returns an explicit field list. Adding
  `'delivery_location' => $this->whenLoaded(...)` is additive; existing consumers see no change
  until they request the eager-loaded relation.
- `OrderController` returns `OrderResource` without eager loading. Using `whenLoaded()` in the
  resource means the field simply omits itself when the relation is not loaded — zero breakage.
- The frontend `processOrder()` payload is an explicit object literal. Spreading conditional
  location fields only when coordinates are available is additive.

One pre-existing gap, unrelated to delivery location but worth fixing in Phase 7:
`notes` is validated in `CheckoutRequest` and sent by the frontend but is never read in
`CheckoutService`. It is silently discarded on every order. Fix: pass `$data['notes']` as an
`OrderComment` after order creation, or store it in `addresses.additional['notes']`.

---

## Database — one new migration

File: `backend/packages/Store/KurdistanStore/database/migrations/YYYY_MM_DD_000001_create_order_delivery_locations_table.php`

```php
Schema::create('order_delivery_locations', function (Blueprint $table) {
    $table->id();
    $table->unsignedInteger('order_id');
    $table->decimal('lat', 10, 7);
    $table->decimal('lng', 10, 7);
    $table->string('formatted_address', 500)->nullable();
    $table->unsignedInteger('accuracy_meters')->nullable(); // from browser coords.accuracy
    $table->enum('source', ['gps', 'manual', 'default'])->default('manual');
    $table->timestamps();

    $table->unique('order_id');
    $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
});
```

`source` values:
- `gps` — browser returned coords and `accuracy_meters` ≤ 1000
- `manual` — user dragged the pin (no GPS, or GPS accuracy > 1000m)
- `default` — user skipped the map; only the text address was captured

`accuracy_meters` lets courier apps decide whether to trust the pin (< 100 is precise;
> 500 means fall back to the text address in `addresses.address`).

---

## Backend — five changes

### 1. Model: `DeliveryLocation`

New file: `src/Models/DeliveryLocation.php`

```php
namespace Store\KurdistanStore\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryLocation extends Model
{
    protected $table = 'order_delivery_locations';

    protected $fillable = ['order_id', 'lat', 'lng', 'formatted_address', 'accuracy_meters', 'source'];

    protected $casts = [
        'lat'            => 'float',
        'lng'            => 'float',
        'accuracy_meters' => 'integer',
    ];
}
```

### 2. Relation on `Order` — no core file edits

Register in `KurdistanStoreServiceProvider::boot()`:

```php
use Webkul\Sales\Models\Order;
use Store\KurdistanStore\Models\DeliveryLocation;

Order::resolveRelationUsing('deliveryLocation', fn (Order $order) =>
    $order->hasOne(DeliveryLocation::class, 'order_id')
);
```

### 3. `CheckoutRequest` — four new nullable rules

```php
'delivery_lat'               => ['nullable', 'numeric', 'between:-90,90'],
'delivery_lng'               => ['nullable', 'numeric', 'between:-180,180'],
'delivery_formatted_address' => ['nullable', 'string', 'max:500'],
'delivery_source'            => ['nullable', 'string', Rule::in(['gps', 'manual', 'default'])],
// browser coords.accuracy in metres — informational, not validated strictly
'delivery_accuracy_meters'   => ['nullable', 'integer', 'min:0'],
```

### 4. `CheckoutService::placeOrder()` — insert after order creation

Insertion point: immediately after `$order = $this->orderRepository->create($orderData);`
and before `$paymentResult = ...`

```php
if (isset($data['delivery_lat'], $data['delivery_lng'])) {
    DeliveryLocation::create([
        'order_id'          => $order->id,
        'lat'               => $data['delivery_lat'],
        'lng'               => $data['delivery_lng'],
        'formatted_address' => $data['delivery_formatted_address'] ?? null,
        'accuracy_meters'   => $data['delivery_accuracy_meters'] ?? null,
        'source'            => $data['delivery_source'] ?? 'manual',
    ]);
}
```

If the insert fails it must not roll back the order. Wrap in `try/catch` and log the failure.

### 5. `KurdistanStore\Http\Resources\OrderResource` — add one field

```php
'delivery_location' => $this->whenLoaded('deliveryLocation', fn () => [
    'lat'               => (float) $this->deliveryLocation->lat,
    'lng'               => (float) $this->deliveryLocation->lng,
    'formatted_address' => $this->deliveryLocation->formatted_address,
    'source'            => $this->deliveryLocation->source,
    'accuracy_meters'   => $this->deliveryLocation->accuracy_meters,
]),
```

`whenLoaded` means the key is absent from responses where the relation was not eager-loaded
(e.g. the `OrderController::index()` list endpoint). Only `OrderController::show()` should
load the relation:

```php
// OrderController::show() — add before returning response
$order->load('deliveryLocation');
```

---

## New backend endpoint — geocoding proxy

Route: `GET /api/v1/geocode?lat={lat}&lng={lng}`
Controller: `GeocodingController::reverse()`
Middleware: existing `api` rate limit (`throttle:120,1`). No auth required.

Purpose: reverse-geocode coordinates server-side so the Google Geocoding API key is never
exposed to the browser. Returns `{ "formatted_address": "..." }` or HTTP 422.

```php
// .env additions
GOOGLE_MAPS_KEY=           // browser: Maps JS API — restrict to HTTP referrer
GOOGLE_GEOCODING_KEY=      // server: Geocoding API — restrict to server IP, never sent to client
```

Fallback: if `GOOGLE_GEOCODING_KEY` is unset, proxy to Nominatim
(`https://nominatim.openstreetmap.org/reverse`) — free, no key, but sparser coverage in
Iraqi Kurdistan.

---

## Frontend — four changes

### 1. `useDeliveryLocation` hook (`src/hooks/useDeliveryLocation.js`)

Encapsulates all browser Geolocation API interaction.

```
status: 'idle' | 'requesting' | 'granted' | 'denied' | 'unavailable' | 'low_accuracy'

onMount:
  navigator.geolocation.getCurrentPosition(success, error, { timeout: 8000 })

  success(pos):
    if pos.coords.accuracy <= 1000  → status='granted', source='gps'
    else                             → status='low_accuracy', source='manual', center=Erbil
                                      // VPNs and IP-fallback return accuracy > 1000 m

  error(err):
    PERMISSION_DENIED  → status='denied', source='manual', center=Erbil
    other              → status='unavailable', source='manual', center=Erbil
```

Default Erbil center: `{ lat: 36.1911, lng: 44.0093 }`.

### 2. `DeliveryMap` component (`src/components/DeliveryMap.jsx`)

Dependency: `@googlemaps/js-api-loader` (npm). Load via `VITE_GOOGLE_MAPS_KEY`.

Props: `onChange({ lat, lng, formattedAddress, source, accuracyMeters })`

Behavior:
- Renders a `google.maps.Map` in a fixed-height `div`.
- Places a draggable `AdvancedMarkerElement` at the detected or default position.
- On `dragend` or `map.click`: calls `GET /api/v1/geocode?lat=X&lng=Y` debounced 600ms.
- On geocode response: calls `onChange` with resolved address.
- If Maps API fails to load: renders `null` — text address field becomes the only input and
  the checkout still works. No error shown to user unless Maps loading fails.

### 3. `CheckoutPage.jsx` — new state and section

Add `locationData` state:
```js
const [locationData, setLocationData] = useState({
  lat: null, lng: null, formattedAddress: null,
  source: 'default', accuracyMeters: null,
});
```

Add a section between "SHIPPING ADDRESS" and "PAYMENT METHOD":
- Renders `<DeliveryMap onChange={setLocationData} />`.
- When the map places a pin and `formattedAddress` is returned, pre-fill the `address`
  textarea: `setFormData(prev => ({ ...prev, address: formattedAddress }))`.
- The `address` field remains editable. It is still required — the map pre-fill is a convenience.

### 4. `processOrder()` payload — conditional spread

```js
const payload = {
  first_name:      formData.firstName,
  last_name:       formData.lastName,
  phone:           formData.phone,
  governorate:     formData.governorate,
  city:            formData.city,
  address:         formData.address,
  notes:           formData.notes,
  payment_method:  paymentMap[paymentMethod] || 'cashondelivery',
  ...(locationData.lat != null && {
    delivery_lat:               locationData.lat,
    delivery_lng:               locationData.lng,
    delivery_formatted_address: locationData.formattedAddress,
    delivery_source:            locationData.source,
    delivery_accuracy_meters:   locationData.accuracyMeters,
  }),
};
```

---

## Admin visibility

Bagisto admin shows addresses from the `addresses` table. `order_delivery_locations` will not
appear there automatically. Two options:

1. **Short-term**: Register a listener on Bagisto's `sales.order.view.info.after` Blade event
   in `KurdistanStoreServiceProvider::boot()` to inject a coordinate/map panel without editing
   any core admin file.

2. **Long-term**: `GET /api/v1/admin/orders/{id}/delivery-location` — admin-authenticated
   endpoint for courier integrations. Do not couple this to the admin Blade events.

---

## Implementation order when ready

1. Migration + `DeliveryLocation` model + `resolveRelationUsing` in service provider  
2. `CheckoutRequest` additions + `CheckoutService` persistence (with try/catch)  
3. `GeocodingController` + route + env vars  
4. `useDeliveryLocation` hook + `@googlemaps/js-api-loader` install  
5. `DeliveryMap` component  
6. `CheckoutPage` integration  
7. `OrderResource` `whenLoaded` addition + `OrderController::show()` eager load  
8. Admin Blade event hook (optional but useful at launch)
