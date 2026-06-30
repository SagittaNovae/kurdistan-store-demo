# Design Decisions

This document explains the architectural choices made in this project and the reasoning behind them.

---

## Stack Overview

| Layer | Technology | Why |
|-------|------------|-----|
| Backend | Laravel 12 via Bagisto 2.4 | See D1 below |
| Frontend | React 19 + Vite + Tailwind CSS v4 | See D2 below |
| Auth | Laravel Sanctum (cookie + Bearer token) | See D3 below |
| Database | MySQL 8 | Standard, well-supported, Bagisto requirement |
| Cache / Queue | Redis (optional; falls back to file/sync) | Speed at scale; not required for demo |
| Localisation | Sorani Kurdish, Arabic, English with RTL | See D6 below |

---

## D1 — Backend: Bagisto (Laravel e-commerce platform)

**Decision:** Use Bagisto 2.4 as the backend foundation rather than building a Laravel application from scratch.

**Rationale:** Bagisto ships a complete, production-tested e-commerce engine: catalog, categories, product variants (EAV), shopping cart, checkout, order management, inventory, customer accounts, wishlists, reviews, and an admin panel. Building these from scratch would have taken weeks and contributed no differentiation. Bagisto handles them; we handle the Iraq-specific parts.

**Trade-off:** Bagisto's architecture (repository pattern, proxy models, module system) adds some learning curve. The benefit is that the custom code layer is thin and focused.

**Convention enforced:** The Bagisto core packages (`packages/Webkul/`) are never modified directly. All custom logic lives in a single isolated package (`packages/Store/KurdistanStore/`), registered as a Composer path repository.

---

## D2 — Headless storefront: React 19 SPA

**Decision:** Build the customer-facing storefront as a React SPA that consumes a custom REST API (`/api/v1`), rather than using Bagisto's built-in Blade/Vue storefront.

**Rationale:**
- Full control over the UI — needed for the dark minimalist design, RTL layout, and Kurdish locale support.
- Bagisto's built-in shop theme would require deep template overrides and would not support the required three-locale RTL setup cleanly.
- A headless architecture means the frontend contract is defined by the API resources, not by Blade templates.

**Trade-off:** More total code to write (custom API + React SPA). Offset by Bagisto handling all the commerce data layer.

---

## D3 — Authentication: Sanctum with refresh token rotation

**Decision:** Use Laravel Sanctum for API authentication with a short-lived access token (in-memory, not localStorage) and a rotating HttpOnly refresh token cookie.

**Why not standard Sanctum session auth?**
Standard Sanctum SPA auth relies on a session cookie and works well for same-domain SPAs. This project separates the frontend (Vite dev server on port 5173) from the backend (Laravel on port 8000) during development, so cookie-based session auth requires explicit CORS and cookie configuration. Using Bearer tokens on the frontend with an HttpOnly refresh cookie on the backend gives:

- The access token never touches `localStorage` (XSS-safe).
- The refresh token is HttpOnly (JS-inaccessible).
- Token rotation means a stolen refresh token is invalidated on next use.

**Implementation:** `TokenService` issues the pair. `AuthController` handles login, refresh, and logout. The `CustomerRefreshToken` model tracks active refresh tokens per customer (hash stored, never plain).

---

## D4 — Phone-primary authentication

**Decision:** Customers register and log in with a phone number + password rather than email + password.

**Rationale:** The target market is Kurdistan/Iraq where WhatsApp and phone numbers are the primary communication channel. Email addresses are less reliably available. Phone-primary auth is standard for local e-commerce and delivery apps in the region.

**Implementation detail:** Bagisto's `customers` table requires a non-null unique email. Phone-only signups generate a placeholder email (`{phone_digits}@phone.demo.local`) internally. This placeholder is never shown to the customer or used for outgoing email — Bagisto simply needs the column to be populated.

**OTP for password reset:** When a user requests a password reset, a one-time code is sent to their phone number. In demo/development mode (`OTP_SANDBOX=true`) the code is written to `storage/logs/laravel.log` instead of sending a real SMS — no provider credentials are needed to test the flow.

---

## D5 — All custom code in one package

**Decision:** Every line of custom code lives in `packages/Store/KurdistanStore/`, registered as a local Composer path repository.

**Rationale:** This follows Bagisto's extension model and enforces a hard boundary between framework code (never touched) and product code (everything we own). The package has its own service provider, routes, controllers, services, models, migrations, and config.

**Structure inside the package:**

```
Http/
  Controllers/Api/   — thin controllers: validate → service → resource
  Requests/          — FormRequest validation per endpoint
  Resources/         — JSON API resources (stable response shape)
Services/
  Auth/              — TokenService, AuthService
  Checkout/          — CheckoutService (cart → order orchestration)
  Payment/           — gateway interface + COD implementation
  Shipping/          — zone lookup and rate calculation
  Phone/             — E.164 normalisation and Iraqi pattern validation
Models/              — custom tables only (refresh tokens, delivery zones, addresses, preferences)
```

**Layering rule:** Controllers are thin. Business logic lives in Services. Data access for Bagisto entities goes through Bagisto repositories; custom tables use Eloquent models.

---

## D6 — Localisation: Kurdish Sorani, Arabic, English with RTL

**Decision:** Support three locales — Sorani Kurdish (`ckb`), Arabic (`ar`), and English (`en`) — with RTL layout for `ckb` and `ar`.

**Rationale:** All three are in active use across the Kurdistan region. Kurdish Sorani is the primary written language of Erbil and Sulaymaniyah; Arabic is dominant in Duhok and among older users; English is used for technical contexts.

**Implementation:**
- `I18nContext` manages the active locale and applies `dir="rtl"` or `dir="ltr"` to the document root.
- Tailwind CSS v4 logical properties (`ms-`, `me-`, `ps-`, `pe-`) are used throughout so layouts mirror correctly under RTL without separate stylesheets.
- String files live in `frontend/src/i18n/` (one object per locale).

---

## D7 — Payment: Cash on Delivery (primary)

**Decision:** Cash on Delivery is the default and primary payment method.

**Rationale:** COD is the dominant payment method for e-commerce in Iraq. A significant portion of the population is unbanked or prefers not to use cards online. Building a checkout that assumes card payment would misfit the target market.

**Architecture:** A `PaymentGatewayInterface` and `PaymentGatewayManager` allow additional gateways to be added without touching checkout code — new gateway = one config entry + one class. In this demo version only COD is active.

---

## D8 — Delivery zones as configuration

**Decision:** Delivery zones (governorate → district → flat rate) are stored in a `delivery_zones` database table seeded from `config/kurdistan-store.php`, rather than being hardcoded in a service.

**Rationale:** Shipping coverage and rates change. Config-driven zones mean the business can update them via the database without a code deployment. The `ShippingService` reads from the database; the seeder populates it from config.

---

## Data model (custom tables)

Bagisto creates its ~200 core tables. The KurdistanStore package adds only:

| Table | Purpose |
|-------|---------|
| `customer_refresh_tokens` | Refresh token rotation — stores token hash, expiry, revoked_at |
| `delivery_zones` | Governorate / district → flat shipping rate |
| `customer_delivery_addresses` | Saved delivery addresses with latitude/longitude |
| `customer_preferences` | Per-user settings (language, notification opt-in) |
| `payment_transactions` | Payment lifecycle record (for future gateway expansion) |
