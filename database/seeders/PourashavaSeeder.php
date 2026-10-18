<?php

namespace Database\Seeders;

use App\Models\Business\Location;
use Illuminate\Database\Seeder;

/**
 * Imports the 328 pourashavas (municipalities) that sit under the upazilas.
 *
 * Source: iqbalhasandev/bangladesh-geo-json (MIT)
 * https://github.com/iqbalhasandev/bangladesh-geo-json
 * The JSON is vendored under database/seeders/data so seeding never needs
 * network. Each pourashava carries its category (A/B/C), but the locations
 * table has no column for it, so only the names are stored.
 *
 * Runs after LocationSeeder (the upazilas must exist first) and before
 * WardSeeder, which generates wards under every pourashava as well as every
 * union. Names are matched to our BBS-derived rows the same way UnionSeeder
 * does it: exact spelling first, then normalised, then the nearest spelling
 * restricted to the correct district - never a guess across districts.
 *
 * Re-running is safe: rows are keyed on parent + name + type, so anything
 * typed in by hand from the app is left alone.
 */
class PourashavaSeeder extends Seeder
{
    private const DATA_FILE = __DIR__.'/data/bd-pourashavas.json';

    /**
     * Source upazila name (normalised) => our stored upazila name.
     *
     * Same three rows UnionSeeder reconciles: our BBS dataset uses the
     * Bangla-English spelling while the pourashava source uses the English
     * one ("Matlab South" is "Matlab Dakkhin", "Nesarabad" carries its
     * alternate name in brackets in our data).
     */
    private const UPAZILA_ALIAS = [
        'matlabsouth' => 'matlab dakkhin',
        'matlabnorth' => 'matlab uttar',
        'nesarabad' => 'nesarabad (swarupkathi)',
    ];

    public function run(): void
    {
        if (! is_file(self::DATA_FILE)) {
            $this->command?->warn('Pourashava source JSON missing; nothing seeded.');

            return;
        }

        $tree = json_decode((string) file_get_contents(self::DATA_FILE), true);

        if (! is_array($tree) || $tree === []) {
            $this->command?->warn('Pourashava source JSON contained no records.');

            return;
        }

        $divisionRow = [];
        foreach (Location::where('type', 'division')->get() as $d) {
            $divisionRow[$this->normalise($d->name)] = $d;
        }

        $districtByDivision = [];
        foreach (Location::where('type', 'district')->get() as $d) {
            $districtByDivision[$d->parent_id][] = $d;
        }

        $upazilaByDistrict = [];
        foreach (Location::where('type', 'upazila')->get() as $u) {
            $upazilaByDistrict[$u->parent_id][] = $u;
        }

        $created = 0;
        $districtsUsed = [];
        $orphans = [];

        foreach ($tree as $division) {
            $divisionModel = $this->pick($division['name'] ?? '', array_values($divisionRow));

            if ($divisionModel === null) {
                $this->skipBranch($division, 'division '.($division['name'] ?? '?'), $orphans);

                continue;
            }

            foreach ($division['districts'] ?? [] as $district) {
                $districtModel = $this->pick(
                    $district['name'] ?? '',
                    $districtByDivision[$divisionModel->id] ?? []
                );

                foreach ($district['upazilas'] ?? [] as $upazila) {
                    $upazilaModel = $districtModel === null
                        ? null
                        : $this->pick($upazila['name'] ?? '', $upazilaByDistrict[$districtModel->id] ?? []);

                    foreach ($upazila['pourashavas'] ?? [] as $pourashava) {
                        $name = trim((string) ($pourashava['name'] ?? ''));

                        if ($name === '') {
                            continue;
                        }

                        if ($upazilaModel === null) {
                            $orphans[] = $name.' (upazila '.($upazila['name'] ?? '?')
                                .', '.($district['name'] ?? '?').')';

                            continue;
                        }

                        Location::updateOrCreate(
                            ['parent_id' => $upazilaModel->id, 'name' => $name, 'type' => 'pourashava'],
                            [
                                'name_bn' => trim((string) ($pourashava['bn_name'] ?? '')) ?: null,
                                'sort_order' => 0,
                            ]
                        );

                        $created++;
                        $districtsUsed[$districtModel->id] = true;
                    }
                }
            }
        }

        $this->command?->info(
            "Seeded {$created} pourashavas across ".count($districtsUsed).' districts.'
        );

        if ($orphans !== []) {
            $this->command?->warn(
                'Could not place '.count($orphans).' pourashavas ('.implode('; ', array_slice($orphans, 0, 12)).')'
            );
        }
    }

    /** Record every pourashava under a branch whose parent could not be matched. */
    private function skipBranch(array $node, string $where, array &$orphans): void
    {
        foreach ($node['districts'] ?? [] as $district) {
            foreach ($district['upazilas'] ?? [] as $upazila) {
                foreach ($upazila['pourashavas'] ?? [] as $pourashava) {
                    if (trim((string) ($pourashava['name'] ?? '')) !== '') {
                        $orphans[] = $pourashava['name'].' ('.$where.')';
                    }
                }
            }
        }
    }

    /**
     * Best Location row for a source name among the candidates.
     *
     * 1. identical spelling, 2. identical once punctuation is dropped,
     * 3. nearest spelling (only ever considered among the same parent's
     * children, so "Raigonj" cannot land on "Ramganj" in another district).
     */
    private function pick(string $sourceName, array $candidates): ?Location
    {
        if ($sourceName === '' || $candidates === []) {
            return null;
        }

        $sourceName = self::UPAZILA_ALIAS[$this->normalise($sourceName)] ?? $sourceName;
        $target = $this->normalise($sourceName);

        foreach ($candidates as $c) {
            if (strcasecmp($c->name, $sourceName) === 0) {
                return $c;
            }
        }

        foreach ($candidates as $c) {
            if ($this->normalise($c->name) === $target) {
                return $c;
            }
        }

        $best = null;
        $bestDist = 4;

        foreach ($candidates as $c) {
            $distance = levenshtein($target, $this->normalise($c->name));
            if ($distance < $bestDist) {
                $bestDist = $distance;
                $best = $c;
            }
        }

        return $best;
    }

    private function normalise(string $name): string
    {
        return preg_replace('/[^a-z]/', '', strtolower(trim($name)));
    }
}
