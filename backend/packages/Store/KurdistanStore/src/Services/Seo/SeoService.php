<?php

namespace Store\KurdistanStore\Services\Seo;

use Illuminate\Support\Facades\Cache;
use Webkul\Product\Repositories\ProductRepository;

class SeoService
{
    public function __construct(protected ProductRepository $productRepository) {}

    public function forProduct(int $productId): array
    {
        $product = $this->productRepository
            ->with(['product_flats', 'images'])
            ->findOrFail($productId);

        $flat = $product->product_flats->first();
        $siteName = config('kurdistan-store.seo.site_name', 'STORE.');
        $title = ($flat->meta_title ?? $flat->name).' | '.$siteName;
        $description = $flat->meta_description ?? strip_tags($flat->short_description ?? '');
        $image = $product->images->first()?->url;
        $url = url('/product/'.($flat->url_key ?? $product->id));

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $url,
            'open_graph' => [
                'og:title' => $title,
                'og:description' => $description,
                'og:image' => $image,
                'og:url' => $url,
                'og:type' => 'product',
                'og:site_name' => $siteName,
            ],
            'structured_data' => [
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                'name' => $flat->name,
                'description' => $description,
                'sku' => $product->sku,
                'image' => $image,
                'offers' => [
                    '@type' => 'Offer',
                    'price' => (float) ($flat->price ?? 0),
                    'priceCurrency' => core()->getCurrentCurrencyCode(),
                    'availability' => 'https://schema.org/InStock',
                ],
            ],
        ];
    }

    public function getSitemapUrls(): array
    {
        return Cache::remember('kurdistan.sitemap', 3600, function () {
            $urls = [
                ['loc' => url('/'), 'changefreq' => 'daily', 'priority' => 1.0],
                ['loc' => url('/browse'), 'changefreq' => 'daily', 'priority' => 0.9],
            ];

            $this->productRepository
                ->getAll(['status' => 1])
                ->each(function ($product) use (&$urls) {
                    $flat = $product->product_flats->first();
                    $urls[] = [
                        'loc' => url('/product/'.($flat->url_key ?? $product->id)),
                        'changefreq' => 'weekly',
                        'priority' => 0.8,
                    ];
                });

            return $urls;
        });
    }

    public function generateSitemapXml(): string
    {
        $urls = $this->getSitemapUrls();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $xml .= '<url>';
            $xml .= '<loc>'.e($url['loc']).'</loc>';
            $xml .= '<changefreq>'.e($url['changefreq']).'</changefreq>';
            $xml .= '<priority>'.e((string) $url['priority']).'</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return $xml;
    }
}
