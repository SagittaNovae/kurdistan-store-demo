<?php

namespace Store\KurdistanStore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Store\KurdistanStore\Services\Seo\SeoService;

class GenerateSitemap extends Command
{
    protected $signature = 'kurdistan:generate-sitemap';

    protected $description = 'Generate sitemap.xml for SEO';

    public function handle(SeoService $seoService): int
    {
        $xml = $seoService->generateSitemapXml();
        $path = public_path('sitemap.xml');

        File::put($path, $xml);

        $this->info("Sitemap written to {$path}");

        return self::SUCCESS;
    }
}
