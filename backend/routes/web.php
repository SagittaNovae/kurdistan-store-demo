<?php

use Illuminate\Support\Facades\Route;

/*
 * Catch-all: serve the React SPA index.html for all non-admin web routes.
 * nginx serves index.html directly for GET / (static file in public/).
 * This route handles client-side React Router paths like /browse, /product/123 etc.
 * Admin panel routes (/admin/*) are handled by Bagisto's AdminServiceProvider.
 */
Route::get('/{any}', function () {
    $indexPath = public_path('spa.html');
    if (! file_exists($indexPath)) {
        return response('Frontend not built. Run: cd frontend && npm run build', 503);
    }

    return response()->file($indexPath);
})->where('any', '^(?!admin|api|sanctum).*');
