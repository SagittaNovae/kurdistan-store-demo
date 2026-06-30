<?php

use Illuminate\Support\Facades\Route;
use Store\KurdistanStore\Http\Controllers\Api\AuthController;
use Store\KurdistanStore\Http\Controllers\Api\ShippingController;
use Store\KurdistanStore\Http\Controllers\Api\CartController;
use Store\KurdistanStore\Http\Controllers\Api\CategoryController;
use Store\KurdistanStore\Http\Controllers\Api\OrderController;
use Store\KurdistanStore\Http\Controllers\Api\ProductController;
use Store\KurdistanStore\Http\Controllers\Api\SeoController;
use Store\KurdistanStore\Http\Controllers\Api\ReviewController;
use Store\KurdistanStore\Http\Controllers\Api\WishlistController;
use Store\KurdistanStore\Http\Controllers\Api\AddressController;
use Store\KurdistanStore\Http\Controllers\Api\ProfileController;
use Store\KurdistanStore\Http\Middleware\CorsMiddleware;

Route::prefix('api/v1')
    ->middleware([CorsMiddleware::class, 'web'])
    ->group(function () {
        Route::get('/health', fn () => response()->json(['status' => 'ok', 'service' => 'kurdistan-store-api']));

        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{id}', [ProductController::class, 'show'])->whereNumber('id');
        Route::get('/products/slug/{slug}', [ProductController::class, 'showBySlug']);
        Route::get('/categories', [CategoryController::class, 'index']);

        Route::get('/shipping/zones', [ShippingController::class, 'zones']);
        Route::get('/shipping/quote', [ShippingController::class, 'quote']);

        Route::get('/products/{id}/reviews', [ReviewController::class, 'index'])->whereNumber('id');

        Route::get('/seo/products/{id}', [SeoController::class, 'product'])->whereNumber('id');
        Route::get('/seo/sitemap', [SeoController::class, 'sitemap']);
        Route::get('/seo/sitemap.xml', [SeoController::class, 'sitemapXml']);

        Route::get('/locations', fn () => response()->json(['data' => config('kurdistan-store.locations')]));

        Route::middleware('throttle:'.config('kurdistan-store.rate_limits.auth', 10).',1')->group(function () {
            Route::post('/auth/register', [AuthController::class, 'register']);
            Route::post('/auth/login', [AuthController::class, 'login']);
        });

        // Token refresh — accepts HttpOnly cookie (web) or body field (mobile); no auth guard required.
        Route::post('/auth/refresh', [AuthController::class, 'refresh']);

        Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum,customer');
        Route::get('/auth/me', [AuthController::class, 'me'])->middleware('auth:sanctum,customer');

        Route::get('/cart', [CartController::class, 'show']);
        Route::post('/cart/items', [CartController::class, 'add']);
        Route::patch('/cart/items/{itemId}', [CartController::class, 'update'])->whereNumber('itemId');
        Route::delete('/cart/items/{itemId}', [CartController::class, 'remove'])->whereNumber('itemId');

        Route::post('/checkout', [CartController::class, 'checkout'])
            ->middleware('throttle:'.config('kurdistan-store.rate_limits.api', 120).',1');

        Route::middleware(['auth:sanctum,customer', 'throttle:'.config('kurdistan-store.rate_limits.api', 120).',1'])->group(function () {
            Route::get('/orders', [OrderController::class, 'index']);
            Route::get('/orders/{id}', [OrderController::class, 'show'])->whereNumber('id');
            Route::get('/wishlist', [WishlistController::class, 'index']);
            Route::post('/wishlist', [WishlistController::class, 'store']);
            Route::delete('/wishlist/{productId}', [WishlistController::class, 'destroy'])->whereNumber('productId');
            Route::post('/products/{id}/reviews', [ReviewController::class, 'store'])->whereNumber('id');

            // Account management
            Route::get('/account/profile', [ProfileController::class, 'show']);
            Route::patch('/account/profile', [ProfileController::class, 'update']);
            Route::post('/account/password', [ProfileController::class, 'changePassword']);
            Route::get('/account/preferences', [ProfileController::class, 'getPreferences']);
            Route::put('/account/preferences', [ProfileController::class, 'updatePreferences']);
            Route::post('/account/logout-all', [ProfileController::class, 'logoutAll']);

            // Delivery addresses
            Route::get('/account/addresses', [AddressController::class, 'index']);
            Route::post('/account/addresses', [AddressController::class, 'store']);
            Route::put('/account/addresses/{id}', [AddressController::class, 'update'])->whereNumber('id');
            Route::delete('/account/addresses/{id}', [AddressController::class, 'destroy'])->whereNumber('id');
            Route::post('/account/addresses/{id}/default', [AddressController::class, 'setDefault'])->whereNumber('id');
        });
    });
