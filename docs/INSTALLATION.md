# Installation Guide

## System Requirements

| Component | Version |
|-----------|---------|
| PHP | 8.3+ |
| Composer | 2.x |
| MySQL / MariaDB | 8.0+ / 10.6+ |
| Node.js | 20+ |
| Redis | 7+ *(optional — file/sync drivers work for local development)* |

---

## Step 1 — Clone the repository

```bash
git clone <your-repo-url> kurdistan-store
cd kurdistan-store
```

---

## Step 2 — Install the Bagisto backend

Bagisto must be installed first. If this is a fresh clone with no `backend/vendor/`:

```bash
cd backend
composer install
```

If `backend/` does not yet contain a Bagisto installation, create one and register the custom package:

```bash
# From the project root:
composer create-project bagisto/bagisto:^2.4 backend --no-interaction
cd backend
composer config repositories.kurdistan-store path packages/Store/KurdistanStore
composer require store/kurdistan-store:@dev --no-interaction
```

---

## Step 3 — Configure the environment

```bash
cd backend
cp .env.example .env
php artisan key:generate
```

Edit `backend/.env` and set your database credentials:

```env
DB_DATABASE=kurdistan_store_demo
DB_USERNAME=root
DB_PASSWORD=your_password
```

All other values have working demo defaults. Leave `OTP_SANDBOX=true` — password reset codes are written to `storage/logs/laravel.log`.

---

## Step 4 — Database setup

```bash
cd backend
php artisan bagisto:install   # Creates tables and a default admin account
php artisan migrate           # Runs KurdistanStore custom migrations
php artisan vendor:publish --tag=kurdistan-store-config --force
php artisan kurdistan:seed-delivery-zones
```

---

## Step 5 — Seed demo data

```bash
php artisan db:seed --class="Store\\KurdistanStore\\Database\\Seeders\\KurdistanStoreSeeder"
```

This creates sample categories, products, and a demo customer account. See the README for demo credentials.

---

## Step 6 — Run the backend

```bash
php artisan serve
```

---

## Step 7 — Install and run the frontend

```bash
cd frontend
cp .env.example .env
npm install
npm run dev
```

**URLs:**

| | URL |
|-|-----|
| Storefront | http://localhost:5173 |
| Bagisto admin | http://localhost:8000/admin |
| API health | http://localhost:8000/api/v1/health |

---

## Optional: Docker development environment

A `docker-compose.yml` is provided with MySQL, Redis, and Mailpit. If you prefer Docker:

```bash
# From backend/
docker-compose up -d
```

Then follow steps 3–7 above, pointing `DB_HOST` to `127.0.0.1` and `DB_PASSWORD` to the value in `docker-compose.yml`.

---

## File permissions (Linux/macOS only)

```bash
cd backend
chmod -R 775 storage bootstrap/cache
php artisan storage:link
```
