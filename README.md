Note: This repository is a simplified portfolio/demo version of a larger e-commerce project. Production integrations such as payment gateways, SMS/OTP, email delivery, and deployment infrastructure have been intentionally removed so the project can be run locally without external services.

# Kurdistan Store — Portfolio Demo

A full-stack e-commerce application built with **React 19** and **Laravel 12 (Bagisto)**, targeting the Kurdistan / Iraq market.

> **Note:** This is a portfolio/demo version of the project. Production integrations (payment gateways, SMS providers, email services) have been removed or replaced with safe demo equivalents. See [What's included](#whats-included) for details.

---

## My Contributions

This repository demonstrates work I implemented across the full stack:

- **React frontend** — SPA with React 19, Vite, Tailwind CSS v4, React Router v7
- **Laravel backend** — custom REST API (`/api/v1`) built as a Bagisto package
- **Authentication** — phone-based login/register with Sanctum access tokens and rotating HttpOnly refresh token cookies
- **Shopping cart & checkout** — session-based cart (guest + authenticated) with full checkout flow
- **Localisation** — English, Sorani Kurdish, and Arabic with RTL layout support using Tailwind logical properties
- **Delivery map** — Leaflet-based address picker with geocoding via Nominatim
- **Address management** — saved delivery addresses with coordinate storage
- **Docker development environment** — MySQL, Redis, and Mailpit via Docker Compose

See [DESIGN_DECISIONS.md](DESIGN_DECISIONS.md) for the architectural reasoning behind major choices.

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Frontend | React 19, Vite 8, Tailwind CSS v4, React Router v7, Lucide React, Leaflet |
| Backend | Laravel 12, Bagisto 2.4, PHP 8.3 |
| Auth | Laravel Sanctum (Bearer token + HttpOnly refresh cookie) |
| Database | MySQL 8 |
| Cache / Session | Redis (optional; file/sync fallback for local dev) |
| Localisation | Sorani Kurdish (ckb), Arabic (ar), English (en) |

---

## What's Included

| Feature | Status |
|---------|--------|
| Product catalog, categories, variants | Via Bagisto + custom API |
| Product browsing, search, filtering | API-backed |
| Product details with reviews | Full implementation |
| User registration / login (phone + password) | Custom auth with token rotation |
| Shopping cart | Session-based, guest + authenticated |
| Checkout with delivery zone selection | COD payment, address with map |
| Order history | Customer-facing read |
| Wishlist | Full CRUD |
| Address management with map picker | Leaflet + Nominatim geocoding |
| Account profile & preferences | Full implementation |
| Password reset | OTP-based — code logged to file in demo mode |
| 3-locale RTL support | ckb / ar / en |
| Admin panel | Bagisto native (`/admin`) |

---

## What's Removed (vs. Production)

The production version includes integrations that are not appropriate for a public portfolio repository:

- **Payment gateways** — FIB (First Iraqi Bank) and Stripe removed; Cash on Delivery remains
- **SMS / OTP providers** — OTPiQ and Twilio removed; password reset uses log-based sandbox
- **Email delivery** — SMTP provider removed; emails are written to `storage/logs/laravel.log`
- **Deployment infrastructure** — VPS scripts, cron configs, systemd units removed

---

## Project Structure

```
├── frontend/     React 19 + Vite storefront
├── backend/      Bagisto (Laravel) application
│   └── packages/Store/KurdistanStore/   ← all custom code
└── docs/         Installation and delivery zone reference
```

---

## Quick Start

### Prerequisites

- PHP 8.3+, Composer 2.x
- MySQL 8+ (or MariaDB 10.6+)
- Node.js 20+
- Redis (optional — file/sync drivers work for local dev)

### 1. Install Bagisto backend

```bash
cd backend
composer install

# Install Bagisto (creates tables, default admin account)
php artisan bagisto:install

# Run custom migrations
php artisan migrate

# Seed demo data
php artisan db:seed --class="Store\\KurdistanStore\\Database\\Seeders\\KurdistanStoreSeeder"
```

If this is a fresh install, run `composer create-project bagisto/bagisto:^2.4 backend` first, then copy in the `packages/Store/KurdistanStore` directory and register the package.

### 2. Configure environment

```bash
cd backend
cp .env.example .env
php artisan key:generate
```

Edit `.env` — set `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` to match your MySQL setup. All other values have working demo defaults.

> **Password reset in demo mode:** `OTP_SANDBOX=true` means the verification code is written to `storage/logs/laravel.log` instead of sent via SMS. After requesting a reset, read the code from the log file.

### 3. Run the frontend

```bash
cd frontend
cp .env.example .env
npm install
npm run dev
```

### 4. Run the backend

```bash
cd backend
php artisan serve
```

**URLs:**
- Storefront: http://localhost:5173
- Admin panel: http://localhost:8000/admin
- API health: http://localhost:8000/api/v1/health

---

## Demo Credentials

After running the seeder:

| Role | Phone | Password |
|------|-------|----------|
| Demo customer | +9647501234567 | password |
| Admin | *(set during `bagisto:install`)* | *(set during install)* |

---

## Admin Panel

Product management, categories, inventory, orders, and coupons are managed through the **Bagisto admin panel** at `/admin`. This panel is provided by Bagisto and is included for demonstration purposes.

---

## Docs

- [Installation Guide](docs/INSTALLATION.md)
- [Delivery Zones Reference](docs/DELIVERY_LOCATION.md)
- [Design Decisions](DESIGN_DECISIONS.md)
