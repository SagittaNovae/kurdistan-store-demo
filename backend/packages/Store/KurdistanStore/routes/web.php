<?php

use Illuminate\Support\Facades\Route;
use Store\KurdistanStore\Services\Seo\SeoService;

Route::get('/sitemap.xml', function (SeoService $seoService) {
    return response($seoService->generateSitemapXml(), 200, [
        'Content-Type' => 'application/xml',
    ]);
});
