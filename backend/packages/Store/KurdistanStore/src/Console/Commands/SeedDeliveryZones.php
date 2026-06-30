<?php

namespace Store\KurdistanStore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedDeliveryZones extends Command
{
    protected $signature = 'kurdistan:seed-zones {--force : Overwrite existing zones}';

    protected $description = 'Seed default Kurdistan delivery zones. Safe to re-run — skips existing rows unless --force.';

    public function handle(): int
    {
        $zones = $this->defaultZones();
        $seeded = 0;
        $skipped = 0;

        foreach ($zones as $zone) {
            $exists = DB::table('delivery_zones')
                ->where('governorate', $zone['governorate'])
                ->where(fn ($q) => $zone['district']
                    ? $q->where('district', $zone['district'])
                    : $q->whereNull('district')
                )
                ->exists();

            if ($exists && ! $this->option('force')) {
                $skipped++;

                continue;
            }

            DB::table('delivery_zones')->updateOrInsert(
                [
                    'governorate' => $zone['governorate'],
                    'district' => $zone['district'],
                ],
                array_merge($zone, [
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );

            $seeded++;
        }

        $this->info("Seeded {$seeded} zones, skipped {$skipped} existing.");

        return self::SUCCESS;
    }

    private function defaultZones(): array
    {
        return [
            // ── Erbil ──────────────────────────────────────────────────────
            ['governorate' => 'Erbil', 'district' => null,           'flat_rate' => 3500, 'estimated_days' => 2],
            ['governorate' => 'Erbil', 'district' => 'Erbil City',   'flat_rate' => 2500, 'estimated_days' => 1],
            ['governorate' => 'Erbil', 'district' => 'Shaqlawa',     'flat_rate' => 4000, 'estimated_days' => 2],
            ['governorate' => 'Erbil', 'district' => 'Soran',        'flat_rate' => 4500, 'estimated_days' => 2],
            ['governorate' => 'Erbil', 'district' => 'Koya',         'flat_rate' => 4000, 'estimated_days' => 2],
            ['governorate' => 'Erbil', 'district' => 'Mergasor',     'flat_rate' => 5500, 'estimated_days' => 3],

            // ── Sulaymaniyah ───────────────────────────────────────────────
            ['governorate' => 'Sulaymaniyah', 'district' => null,                    'flat_rate' => 4000, 'estimated_days' => 2],
            ['governorate' => 'Sulaymaniyah', 'district' => 'Sulaymaniyah City',     'flat_rate' => 3000, 'estimated_days' => 1],
            ['governorate' => 'Sulaymaniyah', 'district' => 'Halabja',               'flat_rate' => 4000, 'estimated_days' => 2],
            ['governorate' => 'Sulaymaniyah', 'district' => 'Ranya',                 'flat_rate' => 4500, 'estimated_days' => 2],
            ['governorate' => 'Sulaymaniyah', 'district' => 'Qaladze',               'flat_rate' => 4500, 'estimated_days' => 2],
            ['governorate' => 'Sulaymaniyah', 'district' => 'Chamchamal',             'flat_rate' => 3500, 'estimated_days' => 2],

            // ── Duhok ──────────────────────────────────────────────────────
            ['governorate' => 'Duhok', 'district' => null,          'flat_rate' => 3500, 'estimated_days' => 2],
            ['governorate' => 'Duhok', 'district' => 'Duhok City',  'flat_rate' => 3000, 'estimated_days' => 1],
            ['governorate' => 'Duhok', 'district' => 'Zakho',       'flat_rate' => 4000, 'estimated_days' => 2],
            ['governorate' => 'Duhok', 'district' => 'Amedi',       'flat_rate' => 4500, 'estimated_days' => 2],
            ['governorate' => 'Duhok', 'district' => 'Semel',       'flat_rate' => 3500, 'estimated_days' => 2],
            ['governorate' => 'Duhok', 'district' => 'Akre',        'flat_rate' => 4000, 'estimated_days' => 2],

            // ── Halabja ────────────────────────────────────────────────────
            ['governorate' => 'Halabja', 'district' => null,             'flat_rate' => 4500, 'estimated_days' => 2],
            ['governorate' => 'Halabja', 'district' => 'Halabja City',   'flat_rate' => 4000, 'estimated_days' => 2],
            ['governorate' => 'Halabja', 'district' => 'Khurmal',        'flat_rate' => 4500, 'estimated_days' => 2],
            ['governorate' => 'Halabja', 'district' => 'Byara',          'flat_rate' => 5000, 'estimated_days' => 3],
        ];
    }
}
