# Checkout & Order Pipeline — Developer Reference

> **Canonical reference** for Kurdistan Store's checkout and order system.  
> Last updated: 2026-06-27.

---

## Table of Contents

1. [Architecture Overview](#1-architecture-overview)
2. [API Endpoints](#2-api-endpoints)
3. [Complete Checkout Flow](#3-complete-checkout-flow)
4. [CheckoutService Responsibilities](#4-checkoutservice-responsibilities)
5. [Order Creation Pipeline](#5-order-creation-pipeline)
6. [Totals Calculation](#6-totals-calculation)
7. [The Cart Refresh Invariant](#7-the-cart-refresh-invariant)
8. [Payment Gateway Model](#8-payment-gateway-model)
9. [Email Order Flow](#9-email-order-flow)
10. [Customer Order Access (Security Model)](#10-customer-order-access-security-model)
11. [Frontend Integration Points](#11-frontend-integration-points)
12. [Architectural Decisions & Trade-offs](#12-architectural-decisions--trade-offs)
13. [Debugging Guide](#13-debugging-guide)
14. [Known Limitations](#14-known-limitations)
15. [Do Not Modify Without Reading This First](#15-do-not-modify-without-reading-this-first)
16. [Recommended Future Enhancements](#16-recommended-future-enhancements)

---

## 1. Architecture Overview

Kurdistan Store's checkout is a **single-step, single-request flow** built on top of Bagisto 2.4's cart infrastructure.

```
React SPA
   │
   │  POST /api/v1/cart/items        (add item)
   │  GET  /api/v1/cart              (preview)
   │  POST /api/v1/checkout          (place order)
   │
   ▼
CartController  ──────────►  CheckoutService
                                │
                         ┌──────┴──────────────────┐
                         │                         │
                    Bagisto Cart              OrderRepository
                    (Cart facade)             (Bagisto Sales)
                         │                         │
                    cart_shipping_rates        orders table
                    cart_items                 order_items
                    cart (table)               order_payment
                                               addresses
                                               order_comments
                                                    │
                                          PaymentGatewayManager
                                                    │
                                          CashOnDelivery / FIB / Stripe
```

**Key packages involved:**

| Package | Role |
|---|---|
| `Store\KurdistanStore` | Custom API layer, CheckoutService, payment gateways |
| `Webkul\Checkout` | Cart facade, Cart model, shipping rate collection |
| `Webkul\Sales` | OrderRepository, OrderResource transformer, Invoice |
| `Webkul\Shipping` | FlatRate and Free carrier implementations |
| `Webkul\Shop` | Order-created email notification listener |
| `Webkul\Admin` | Admin email notification listener |

---

## 2. API Endpoints

All routes are defined in [`packages/Store/KurdistanStore/routes/api.php`](../routes/api.php) under the `api/v1` prefix with the `web` and `CorsMiddleware` middleware stack.

### Cart

| Method | Path | Auth | Description |
|---|---|---|---|
| `GET` | `/api/v1/cart` | Optional | Retrieve current cart (calls `collectTotals`) |
| `POST` | `/api/v1/cart/items` | Optional | Add a product to cart |
| `PATCH` | `/api/v1/cart/items/{itemId}` | Optional | Update item quantity (qty < 1 removes) |
| `DELETE` | `/api/v1/cart/items/{itemId}` | Optional | Remove item from cart |

### Checkout

| Method | Path | Auth | Description |
|---|---|---|---|
| `POST` | `/api/v1/checkout` | Guest: OTP-verified · Customer: token | Place order — the entire checkout in one request |

### Orders

| Method | Path | Auth | Description |
|---|---|---|---|
| `GET` | `/api/v1/orders` | Required (`auth:sanctum,customer`) | List all orders for the authenticated customer |
| `GET` | `/api/v1/orders/{id}` | Required | Retrieve a single order (404 if not owned) |

### Cart persistence

The cart is identified by:
- **Authenticated customer** — `cart.customer_id = customer.id, is_active = 1` (looked up on every request via the `web` middleware).
- **Guest** — `session('cart')->id` stored in the PHP session (cookie-based). Guests must complete OTP verification (`POST /api/v1/auth/verify-otp?mode=guest_checkout`) before the checkout endpoint accepts their request.

---

## 3. Complete Checkout Flow

### Step-by-step trace of `POST /api/v1/checkout`

```
1. CartController::checkout()
   ├── Validates request via CheckoutRequest
   │     Fields: first_name, last_name, phone, governorate, city,
   │             address, payment_method, delivery_latitude/longitude,
   │             delivery_address_text (optional), notes (optional)
   │
   ├── Guest phone-verification guard
   │     • If not authenticated: verifies cart->additional['phone_verified_at']
   │       is present and ≤ 30 minutes old, and submitted phone matches
   │       the OTP-verified phone.
   │     • Authenticated customers bypass this check entirely.
   │
   └── Calls CheckoutService::placeOrder($data)

2. CheckoutService::placeOrder()
   ├── 2a. Cart::saveAddresses()           — saves billing + shipping address
   │         Internally calls resetShippingMethod() which wipes any
   │         stale shipping rates so the next step starts clean.
   │
   ├── 2b. Cart::saveShippingMethod('flatrate_flatrate')
   │         Calls Shipping::isMethodCodeExists() → Shipping::collectRates()
   │         which calculates FlatRate amounts and persists them to
   │         cart_shipping_rates. Returns false if carrier is disabled.
   │
   ├── 2c. Cart::savePaymentMethod(['method' => $code])
   │         Writes a cart_payment row to DB.
   │
   ├── 2d. Cart::collectTotals()
   │         Recomputes sub_total, tax_total, discount, shipping, grand_total.
   │         Internally calls refreshCart() — see §7.
   │
   ├── 2e. $cart = Cart::getCart()          ← CRITICAL re-fetch after step 2d
   │         Returns the freshly-computed cart object. Without this, the
   │         local $cart variable would still point to the pre-shipping
   │         object and grand_total would be wrong.
   │
   ├── 2f. $cart->load('payment')
   │         Loads the payment relation saved in step 2c (it was written to
   │         DB but never hydrated on the in-memory model).
   │
   ├── 2g. OrderResource($cart)->jsonSerialize()
   │         Bagisto's transformer converts the cart into the array that
   │         OrderRepository::create() expects.
   │
   ├── 2h. OrderRepository::create($orderData)
   │         Persists: orders, order_items, order_payment, addresses rows.
   │         Dispatches 'checkout.order.save.after' event.
   │
   ├── 2i. Persist delivery coordinates (if provided)
   │         Direct DB::table('addresses')->update() to bypass fillable guards.
   │         Adds a system order comment with the Google Maps link.
   │
   ├── 2j. Persist customer notes (if provided)
   │         Adds another order comment.
   │
   ├── 2k. PaymentGatewayManager::gateway($code)->initiate([...])
   │         Initiates payment with order->grand_total (now correct).
   │         For COD: creates a payment_transactions row.
   │
   ├── 2l. Cart::deActivateCart()
   │         Sets cart.is_active = 0. Customer's next request starts a fresh cart.
   │
   └── Returns $order with $order->payment_result attached.

3. CartController returns 201 JSON with OrderResource (KurdistanStore).
```

---

## 4. CheckoutService Responsibilities

[`src/Services/Checkout/CheckoutService.php`](../src/Services/Checkout/CheckoutService.php)

This service owns the **entire order-placement transaction** for the Kurdistan Store storefront. It is the single point that orchestrates:

| Responsibility | How |
|---|---|
| Address persistence | `Cart::saveAddresses()` |
| Shipping rate selection | `Cart::saveShippingMethod('flatrate_flatrate')` — hardcoded; there is currently no shipping selection step |
| Payment method selection | `Cart::savePaymentMethod([...])` |
| Total calculation | `Cart::collectTotals()` |
| Cart-to-order transformation | `new OrderResource($cart)->jsonSerialize()` |
| Order persistence | `OrderRepository::create()` |
| Delivery location metadata | Direct SQL on `addresses` + order comment |
| Customer notes | Order comment |
| Payment gateway initiation | `PaymentGatewayManager::gateway()->initiate()` |
| Cart teardown | `Cart::deActivateCart()` |

**What CheckoutService does NOT do:**

- Email sending (handled by Bagisto's `checkout.order.save.after` event listeners)
- Invoice creation (also an event listener in `Webkul\Payment`)
- Stock decrement (Bagisto core via order item listeners)
- Order ID generation (Bagisto core in `OrderRepository::create()`)

---

## 5. Order Creation Pipeline

### OrderRepository::create()

[`packages/Webkul/Sales/src/Repositories/OrderRepository.php`](../../../Webkul/Sales/src/Repositories/OrderRepository.php) (Bagisto core — do not modify)

```
OrderRepository::create($data)
├── Dispatches 'checkout.order.save.before'
├── Generates increment_id (order number)
├── Creates orders row
├── Foreach $data['items']:
│    ├── Dispatches 'checkout.order.orderitem.save.before'
│    ├── Creates order_items row
│    └── Dispatches 'checkout.order.orderitem.save.after'
├── Creates order_payment row from $data['payment']
├── Creates billing address from $data['billing_address']
├── Creates shipping address from $data['shipping_address']  (if stockable items)
└── Dispatches 'checkout.order.save.after'
```

### 'checkout.order.save.after' Listeners

This event triggers all post-creation side effects:

| Listener | Effect |
|---|---|
| `Webkul\Shop\Listeners\Order::afterCreated` | Sends customer confirmation email (if enabled in admin config) |
| `Webkul\Admin\Listeners\Order::afterCreated` | Sends admin new-order notification email |
| `Webkul\Payment\Listeners\GenerateInvoice::handle` | Auto-creates invoice for certain payment methods |
| `Webkul\Notification\Listeners\Order::createOrder` | Creates an admin notification badge |
| `Webkul\Product\Listeners\*` | Decrements inventory |
| `Webkul\CartRule\Listeners\*` | Records coupon usage |

**These run synchronously in the same HTTP request.** If email sending fails, the exception is caught and reported — the order creation is NOT rolled back. Consider queueing emails for production resilience.

---

## 6. Totals Calculation

### Cart-level (`Cart::collectTotals()`)

[`packages/Webkul/Checkout/src/Cart.php:849`](../../../Webkul/Checkout/src/Cart.php)

```
collectTotals():
  1. calculateItemsTax()       — per-item tax based on tax rules
  2. calculateShippingTax()    — shipping tax if configured
  3. refreshCart()             — reload from DB (IMPORTANT: see §7)
  4. Iterate items:
       sub_total  += item.total
       tax_total  += item.tax_amount
       discount   += item.discount_amount
  5. grand_total = sub_total + tax_total - discount_amount
  6. IF selected_shipping_rate exists:
       shipping_amount += rate.price
       tax_total       += rate.tax_amount
       grand_total     += rate.price + rate.tax_amount - rate.discount_amount
  7. Round all values to 2 decimal places
  8. cart.save()
```

### Flat Rate Shipping

[`packages/Webkul/Shipping/src/Carriers/FlatRate.php`](../../../Webkul/Shipping/src/Carriers/FlatRate.php)  
[`packages/Webkul/Shipping/src/Config/carriers.php`](../../../Webkul/Shipping/src/Config/carriers.php)

```php
// Current configuration (hardcoded PHP, not in DB):
'flatrate' => [
    'active'       => true,
    'default_rate' => '10',    // IQD per stockable unit
    'type'         => 'per_unit',
]
```

Shipping formula: `rate = default_rate × qty_ordered (stockable items only)`

Examples:
- 1× keyboard (qty 1) → 10 IQD
- 2× keyboards (qty 2) → 20 IQD
- 1× keyboard + 1× mouse → 20 IQD

> **Note:** The `CartResource` (returned by `GET /api/v1/cart`) shows a shipping preview using `selected_shipping_rate?.price ?? config('kurdistan-store.shipping.flat_rate', 5)`. This fallback of 5 is a display-only estimate shown before a shipping method is set; it has no effect on the actual order total.

### Order-level

Once persisted, the order stores all components independently:

| Column | Source |
|---|---|
| `orders.sub_total` | Cart sub_total |
| `orders.shipping_amount` | Selected shipping rate price |
| `orders.tax_amount` | Cart tax_total |
| `orders.discount_amount` | Cart discount_amount |
| `orders.grand_total` | Cart grand_total (must equal sub + tax − disc + shipping) |

Invoices recalculate their own `grand_total` independently: `invoice.grand_total = invoice.sub_total + invoice.shipping_amount + invoice.tax_amount - invoice.discount_amount`.

---

## 7. The Cart Refresh Invariant

> **This section documents a subtle but critical behavior. Any developer touching `CheckoutService::placeOrder()` must read this.**

### What `refreshCart()` does

`Cart::collectTotals()` calls `$this->refreshCart()` internally ([Cart.php:868](../../../Webkul/Checkout/src/Cart.php)):

```php
public function refreshCart(): void
{
    $this->cart = $this->cartRepository->find($this->cart->id);
    //            ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    //            Replaces the singleton's $this->cart with a
    //            BRAND-NEW Eloquent model instance from the DB.
}
```

After this call, the Cart facade's internal `$this->cart` pointer has changed. Any **local PHP variable** that was assigned `Cart::getCart()` before `collectTotals()` is now stale — it holds a reference to the old object, which has the pre-shipping `grand_total`.

### The bug this caused (pre-fix)

```php
$cart = Cart::getCart();            // Object A — grand_total = sub_total

Cart::saveShippingMethod(...);      // rate saved to DB
Cart::collectTotals();              // refreshCart() → singleton now holds Object B
                                    // Object B.grand_total = sub_total + shipping ✓
                                    // Object A.grand_total = sub_total             ✗

$orderData = new OrderResource($cart); // $cart is still Object A!
// → order.grand_total stored WITHOUT shipping
// → payment gateway charged WITHOUT shipping
```

### The fix

```php
Cart::collectTotals();
$cart = Cart::getCart();   // Re-fetch Object B from the singleton
$cart->load('payment');    // Load payment relation (not eager-loaded by refreshCart)
```

`Cart::getCart()` costs no additional DB query — it simply returns the singleton's current `$this->cart` reference (Object B), which already holds the correct computed totals.

### Why this only surfaced in Kurdistan Store (not standard Bagisto)

Standard Bagisto shop splits checkout into multiple HTTP requests (save-address → get-shipping-rates → save-shipping → save-payment → place-order). Each request calls `Cart::initCart()` fresh, so the stale-reference problem never arises. Kurdistan Store's single-step checkout collapses all steps into one method call in one request, which is where the object-identity issue becomes observable.

---

## 8. Payment Gateway Model

### Interface

[`src/Services/Payment/Contracts/PaymentGatewayInterface.php`](../src/Services/Payment/Contracts/PaymentGatewayInterface.php)

```php
interface PaymentGatewayInterface
{
    public function initiate(array $orderData): array;   // Create payment intent
    public function verify(string $reference, array $payload = []): array; // Verify status
    public function getCode(): string;
}
```

### Gateway registry

Configured in [`config/kurdistan-store.php`](../config/kurdistan-store.php) under `payments.gateways`. Each gateway entry has:

```php
'code' => [
    'title'  => '...',
    'class'  => SomeGateway::class,
    'active' => true|false,          // false gates skip initiate()
    // ...gateway-specific credentials...
]
```

`PaymentGatewayManager::gateway($code)` resolves the class and throws `InvalidArgumentException` if the gateway is inactive or unknown.

### Current gateway status

| Code | Status | Notes |
|---|---|---|
| `cashondelivery` | Active | Creates a `payment_transactions` row with `status=pending` |
| `fib` | Inactive | `FIB_PAYMENT_ENABLED=false` — credentials needed before enabling |
| `stripe` | Inactive | `STRIPE_ENABLED=false` — credentials needed before enabling |

### Payment data flow

```
CheckoutService
  → gateway->initiate(['order_id', 'customer_id', 'amount' => $order->grand_total, 'currency'])
  → $order->payment_result = result array
  → returned to CartController → returned to frontend in JSON response
```

The `payment_result` is not persisted to the `orders` table; it is returned in the checkout response only. The `payment_transactions` table (custom) holds the permanent record.

---

## 9. Email Order Flow

### Customer confirmation email

**Trigger:** `checkout.order.save.after` event  
**Listener:** `Webkul\Shop\Listeners\Order::afterCreated()`  
**Mail class:** `Webkul\Shop\Mail\Order\CreatedNotification`  
**Template:** `packages/Webkul/Shop/src/Resources/views/emails/orders/created.blade.php`

The email is sent **synchronously** during the checkout HTTP request. If the SMTP connection times out, a PHP exception is caught and reported, but the order has already been committed to the database.

The template reads values directly from the `$order` Eloquent model:
- `$order->sub_total` (with and without tax)
- `$order->shipping_amount` (from `orders.shipping_amount`)
- `$order->discount_amount`
- `$order->grand_total` (from `orders.grand_total`)

Because all these are now correctly stored post-fix, the email shows the correct total.

### Admin new-order notification

**Listener:** `Webkul\Admin\Listeners\Order::afterCreated()`  
**Template:** `packages/Webkul/Admin/src/Resources/views/emails/orders/created.blade.php`  
Same data source as the customer email. Enabled via `emails.general.notifications.emails.general.notifications.new_order_mail_to_admin` in admin config.

### Email configuration

SMTP is configured via `emails.configure.smtp.*` keys in the DB (`core_config` table). In demo mode the log mailer is used; configure an SMTP provider in .env for real delivery.

---

## 10. Customer Order Access (Security Model)

### Authentication

All order-reading endpoints require `auth:sanctum,customer` (Sanctum token guard).

The customer guard is a Laravel session guard (`driver: session, provider: customers`). Sanctum adds stateless token support on top. A customer may be authenticated via:
- Session cookie (browser-based, via the `web` middleware)
- Bearer token (mobile/API clients, via `auth:sanctum`)

### Authorization (order ownership)

`OrderController::show()` enforces ownership:

```php
if ((int) $order->customer_id !== (int) $customer->id) {
    abort(404);   // ← 404, not 403
}
```

Returning 404 (rather than 403) prevents enumeration: an attacker probing sequential order IDs cannot distinguish "order exists but is not yours" from "order does not exist."

### Guest checkout security

Guest checkout requires OTP phone verification within 30 minutes:

```
POST /api/v1/auth/send-otp          → sends code to phone
POST /api/v1/auth/verify-otp        → verifies code; writes
                                       cart->additional['phone_verified_at']
                                       cart->additional['verified_phone']
POST /api/v1/checkout               → CartController checks both fields;
                                       rejects if >30 min old or phone mismatch
```

The submitted phone is compared against the OTP-verified phone **after E.164 normalization** (handled in `CheckoutRequest::prepareForValidation()`). This prevents a customer from OTP-verifying one number and submitting a different one.

Guest orders are associated with the email address `{phone}@noreply.local` when no customer account exists.

### Rate limiting

| Endpoint group | Limit |
|---|---|
| Auth endpoints (`/auth/*`) | 10 requests per minute (configurable via `AUTH_RATE_LIMIT`) |
| All other API endpoints | 120 requests per minute (configurable via `API_RATE_LIMIT`) |
| `POST /api/v1/checkout` | Shares the 120/min API limit |

---

## 11. Frontend Integration Points

### Cart preview during browsing

`GET /api/v1/cart` → `CartResource`

The `shipping_amount` in this response is a **display estimate only**:
```php
'shipping_amount' => $this->selected_shipping_rate?->price
                     ?? config('kurdistan-store.shipping.flat_rate', 5)
```
Before a shipping method is selected (i.e., during browsing), the fallback `5` (or whatever `SHIPPING_FLAT_RATE` is set to) is shown. This does not affect the actual order total, which is computed server-side in `placeOrder()`.

### Checkout submission

The frontend submits all checkout data in a single `POST /api/v1/checkout` with the fields from `CheckoutRequest`. There is no multi-step server interaction (no separate "save address" or "select shipping" API calls).

### Order confirmation response

On success the response is `201` with the order serialised by `Store\KurdistanStore\Http\Resources\OrderResource`:

```json
{
  "data": {
    "id": 26,
    "increment_id": "26",
    "status": "pending",
    "sub_total": 34000.0,
    "sub_total_formatted": "IQD 34,000.00",
    "shipping_amount": 10.0,
    "shipping_amount_formatted": "IQD 10.00",
    "discount_amount": 0.0,
    "tax_amount": 0.0,
    "grand_total": 34010.0,
    "grand_total_formatted": "IQD 34,010.00",
    "shipping_method": "flatrate_flatrate",
    "payment_method": "cashondelivery",
    "items": [...],
    "shipping_address": { "name": "...", "city": "Erbil City", ... },
    "billing_address": { ... }
  },
  "message": "Order placed successfully."
}
```

The response also carries `payment_result` directly on the `$order` object (not in the `OrderResource` serialization). The controller merges it into the response if needed, or the frontend can use the order ID to look up the order.

### Order history and detail

`GET /api/v1/orders` — list, sorted descending by created_at  
`GET /api/v1/orders/{id}` — detail with items, addresses, shipments

---

## 12. Architectural Decisions & Trade-offs

### Decision 1: Single-step checkout

**What:** All checkout steps (address, shipping, payment, order creation) happen in one `POST /api/v1/checkout` request instead of the multi-step Bagisto shop flow.

**Why:** Simpler frontend, fewer round trips, better for mobile clients in low-connectivity environments. Works well for a single, fixed shipping method and a small set of payment options.

**Trade-off:** Reuses Bagisto's cart infrastructure which was designed for multi-step flows. This exposed the stale-cart-reference bug (see §7). The single-step approach also means there is no server-side shipping rate selection step; the shipping method is hardcoded to `flatrate_flatrate`.

### Decision 2: Shipping method hardcoded to flatrate

**What:** `Cart::saveShippingMethod('flatrate_flatrate')` is called unconditionally in `CheckoutService`.

**Why:** There is currently only one shipping carrier (FlatRate). Adding a shipping-selection step to the API before the customer can order adds friction with no benefit.

**Trade-off:** If a second carrier is added (e.g., zone-based pricing, express delivery), the single-step checkout will need a new `shipping_method` field in `CheckoutRequest`, or a pre-checkout endpoint to fetch rates. Any new carrier must be registered in `carriers.php` and will be automatically collected by `Shipping::collectRates()`.

### Decision 3: Payment gateway initiation is synchronous

**What:** `PaymentGatewayManager::gateway()->initiate()` is called synchronously inside `placeOrder()`.

**Why:** For COD this is trivially fast (one DB insert). FIB and Stripe require network round-trips.

**Trade-off:** If FIB or Stripe are enabled and the external API is slow or down, the checkout request will block or fail. Consider moving external payment initiation to a queued job when activating FIB/Stripe.

### Decision 4: Order creation uses Bagisto's OrderResource transformer

**What:** `new \Webkul\Sales\Transformers\OrderResource($cart)` converts the cart into the array for `OrderRepository::create()`.

**Why:** This is Bagisto's designed extension point. It ensures all Bagisto-expected fields are populated correctly, including those that are conditionally set (e.g., shipping fields are only included if `haveStockableItems()` is true).

**Trade-off:** Changes to this transformer in a Bagisto upgrade will directly affect order creation. Review `packages/Webkul/Sales/src/Transformers/OrderResource.php` when upgrading Bagisto.

### Decision 5: 404 instead of 403 for unauthorized order access

**What:** `OrderController::show()` returns 404 when the order does not belong to the requesting customer.

**Why:** Returning 403 would confirm to an attacker that an order with that ID exists. 404 reveals nothing.

**Trade-off:** A legitimate customer who misremembers their order ID would get an unhelpful 404. This is an accepted security trade-off.

---

## 13. Debugging Guide

### Problem: Grand total does not include shipping

**Check first:** Is the order from before the fix was deployed? Orders 1–25 have this bug as a historical artifact. New orders (≥ 26) should be correct.

**If a new order is wrong:** Add temporary logging to `CheckoutService::placeOrder()`:

```php
Cart::collectTotals();
$cart = Cart::getCart();
\Log::debug('checkout.totals', [
    'cart_id' => $cart->id,
    'sub_total' => $cart->sub_total,
    'shipping_amount' => $cart->shipping_amount,
    'grand_total' => $cart->grand_total,
    'shipping_method' => $cart->shipping_method,
    'selected_rate' => $cart->selected_shipping_rate?->price,
]);
```

Verify that `shipping_amount > 0` and `grand_total = sub_total + shipping_amount`.

### Problem: `saveShippingMethod` returning false

This means `Shipping::isMethodCodeExists('flatrate_flatrate')` returned false. Possible causes:

1. **FlatRate carrier is disabled** — check `carriers.php`: `'active' => true`.
2. **No shipping address** — `saveAllShippingRates()` skips if `$cart->shipping_address` is null. Verify `Cart::saveAddresses()` was called before `saveShippingMethod()` and that the billing address has `use_for_shipping = true`.
3. **Cart has no stockable items** — `updateOrCreateShippingAddress()` skips non-stockable carts, so no shipping address is saved. This would only affect virtual/downloadable products.

### Problem: Payment gateway throws InvalidArgumentException

`PaymentGatewayManager::gateway($code)` throws if the gateway code is not in `config('kurdistan-store.payments.gateways')` or if its `active` flag is false. Check `CheckoutRequest::rules()` — only `cashondelivery`, `fib`, and `stripe` are valid values; adding a new gateway requires updating both the config and the validation rule.

### Problem: Order created but no email received

1. Verify `emails.general.notifications.emails.general.notifications.new_order` is `1` in the admin config panel.
2. Check `storage/logs/laravel.log` for SMTP errors. The listener catches exceptions with `report()` so the order still creates.
3. Verify SMTP credentials in admin Settings → Emails → Configure.

### Problem: Addresses are not saved on the order

Bagisto creates shipping addresses only if `$cart->haveStockableItems()` is true at the time `OrderRepository::create()` runs. Verify that the product type is `simple` (not `virtual` or `downloadable`).

### Useful Tinker commands for debugging

```php
// Inspect a suspect order
$o = \Webkul\Sales\Models\Order::with(['items','payment','addresses'])->find(ID);
dump($o->sub_total, $o->shipping_amount, $o->grand_total);

// Check active carts for a customer
\Illuminate\Support\Facades\DB::table('cart')
    ->where('customer_id', CUSTOMER_ID)->where('is_active',1)->get();

// Check shipping rates on a cart
\Illuminate\Support\Facades\DB::table('cart_shipping_rates')
    ->where('cart_id', CART_ID)->get();

// Verify flat rate config
config('carriers.flatrate');
```

---

## 14. Known Limitations

### 1. No coupon/discount support in the custom checkout

`CheckoutRequest` does not accept a `coupon_code` field. Cart rules and coupons configured in Bagisto admin are not applied during the custom single-step checkout. The `CartResource` response includes `coupon_code` for future use.

### 2. Cart display shipping estimate may differ from order shipping

`GET /api/v1/cart` shows a shipping preview using a configured `SHIPPING_FLAT_RATE` fallback. The actual shipping amount on the order is calculated by Bagisto's `FlatRate` carrier at checkout time. If the FlatRate is configured as `per_unit` (current default), the estimate will only be correct for single-unit carts.

### 3. Historical orders 1–25 have incorrect grand_total

Orders placed before 2026-06-27 have `grand_total = sub_total` (shipping missing). Their `shipping_amount` field is correct. These orders were already charged at the wrong (lower) amount via the payment gateway. Do not update `grand_total` on these rows without confirming what was actually collected from the customer.

### 4. Email delivery is synchronous

Customer confirmation emails are sent during the checkout HTTP request. If SMTP is slow, this adds latency to the checkout response. If SMTP is down, the order is created but no email is sent (the error is logged, not surfaced to the customer).

### 5. No order cancellation via the customer API

Customers cannot cancel orders through the API. Cancellation is admin-only via Bagisto's admin panel (`POST /admin/sales/orders/cancel/{id}`).

### 6. Guest orders are not linkable to accounts

Guest orders use `{phone}@noreply.local` as the customer email and have `customer_id = null`. They cannot be associated with a customer account after placement. Customers who create an account after a guest order will not see their prior orders in order history.

---

## 15. Do Not Modify Without Reading This First

### `Cart::collectTotals()` and the re-fetch pattern

Never move `$cart = Cart::getCart()` to before `Cart::collectTotals()`. The local variable must be obtained **after** `collectTotals()` returns, because `collectTotals()` calls `refreshCart()` which replaces the Cart singleton's internal pointer. See §7 for the full explanation.

### `Webkul\Sales\Transformers\OrderResource`

This Bagisto-core transformer converts a cart object into the data array for `OrderRepository::create()`. Two behaviors are critical:

1. `grand_total` (line 58) is read from `$this->grand_total` — this is the cart's computed grand_total, which must include shipping.
2. Shipping fields (`shipping_amount`, `shipping_title`, etc.) are only included if `$this->haveStockableItems()` returns true. If this condition becomes false for any cart, those fields are silently absent from the order data (shipping_amount defaults to 0).

Do not bypass this transformer. Do not pass a stale cart object to it.

### `Shipping::isMethodCodeExists()` side effect

Calling `Shipping::isMethodCodeExists($code)` has the side effect of calling `Shipping::collectRates()`, which **deletes and rewrites** all shipping rates for the current cart. This is by design — it ensures rates are always fresh. Be aware of this if you call this method in any context other than `saveShippingMethod()`.

### `Cart::saveAddresses()` always calls `resetShippingMethod()`

`Cart::saveAddresses()` unconditionally calls `$this->resetShippingMethod()` at the end, which wipes all shipping rates from `cart_shipping_rates` and sets `cart.shipping_method = null`. This is why `saveShippingMethod()` must always be called **after** `saveAddresses()`, never before.

### `CheckoutRequest` validation for payment method

The `payment_method` field is validated against an explicit allowlist: `Rule::in(['cashondelivery', 'fib', 'stripe'])`. Adding a new gateway to `kurdistan-store.php` alone is not sufficient — the validation rule must also be updated.

---

## 16. Recommended Future Enhancements

### Short-term

**Queue order emails.** Move `Webkul\Shop\Mail\Order\CreatedNotification` to a queued Mailable. This decouples checkout latency from SMTP performance and makes the system more resilient to mail server downtime.

**Add shipping amount to `CartResource` accurately.** The `GET /api/v1/cart` response currently uses a fallback estimate for `shipping_amount`. Call `Shipping::collectRates()` if a shipping address is available on the cart and return the actual rate instead.

**Idempotency key on checkout.** Accept a client-generated `idempotency_key` in `CheckoutRequest`. Store it in `orders.additional` (or a dedicated column) and return the existing order if a duplicate key is submitted. This prevents double-orders from network retries.

### Medium-term

**Activate FIB or Stripe.** When activating an online payment gateway:
1. Set the gateway's `active` env var to true.
2. Move gateway initiation to a queued job to avoid timeout risk.
3. Build a `POST /api/v1/payments/{gateway}/verify` flow for webhook-based status updates.
4. Update `CheckoutRequest` to allow the gateway code.

**Zone-based or per-governorate shipping.** The FlatRate carrier charges per stockable unit regardless of location. A future carrier could read the shipping address's governorate and apply different rates. This would require adding a `shipping_method` field to `CheckoutRequest` and building a `GET /api/v1/shipping/rates` endpoint.

**Guest order lookups.** Allow guests to look up their orders by order number + phone. This requires a dedicated endpoint that authenticates via the order's phone/increment_id rather than a customer token.

**Cart expiry.** Active carts never expire. If a customer abandons a cart after OTP verification (but before placing an order), the verified phone remains in `cart->additional` until the cart is reused. Implement a scheduled job to deactivate carts older than N days.

### Long-term

**Asynchronous order creation.** For very high order volumes, consider publishing a `CheckoutInitiated` event to a queue and creating the order in a worker. The checkout endpoint returns a `202 Accepted` with an order reference, and the customer polls or uses a webhook to get the final order ID.

**Coupon/discount support.** Extend `CheckoutRequest` to accept a `coupon_code`. Call `Cart::applyCoupon($code)` before `collectTotals()`. The `CartResource` already exposes `coupon_code` in anticipation of this.
