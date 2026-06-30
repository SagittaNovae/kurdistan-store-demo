<?php

namespace Store\KurdistanStore\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Store\KurdistanStore\Services\Seo\SeoService;

class SeoController extends Controller
{
    public function __construct(protected SeoService $seoService) {}

    public function product(int $id): JsonResponse
    {
        return response()->json(['data' => $this->seoService->forProduct($id)]);
    }

    public function sitemap(): JsonResponse
    {
        return response()->json(['data' => $this->seoService->getSitemapUrls()]);
    }

    public function sitemapXml(): Response
    {
        return response($this->seoService->generateSitemapXml(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
