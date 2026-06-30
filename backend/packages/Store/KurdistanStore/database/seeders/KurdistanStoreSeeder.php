<?php

namespace Store\KurdistanStore\Database\Seeders;

use Illuminate\Database\Seeder;

class KurdistanStoreSeeder extends Seeder
{
    /**
     * Catalog is managed entirely through the Bagisto admin panel at /admin.
     * This seeder is intentionally a no-op — create products, categories,
     * attributes, and inventory through the admin UI, not programmatically.
     */
    public function run(): void
    {
        $this->command?->info('KurdistanStore: catalog is managed via Bagisto admin at /admin.');
        $this->command?->info('Create products, categories, and inventory there.');
    }
}
