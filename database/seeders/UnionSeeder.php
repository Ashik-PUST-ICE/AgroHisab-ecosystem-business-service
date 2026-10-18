<?php

namespace Database\Seeders;

use App\Models\Business\Location;
use Illuminate\Database\Seeder;

/**
 * Imports the 4,540 unions that sit under the upazilas.
 *
 * Source: nuhil/bangladesh-geocode (MIT) https://github.com/nuhil/bangladesh-geocode
 * The CSVs are vendored under database/seeders/data so this never needs network.
 *
 * Runs after LocationSeeder, because a union attaches to an upazila that the
 * other seeder created. The two sources spell many places differently
 * ("Sadarsouth" vs "Sadar Dakkhin"), so names are reconciled through the alias
 * tables below. A fuzzy match is only ever considered inside the correct
 * district, otherwise "Raigonj" happily matches "Ramganj" in a different one.
 */
class UnionSeeder extends Seeder
{
    /**
     * Source name (normalised) => our stored name.
     *
     * Two entries look wrong but are not: the source spells them the older way
     * and the BBS dataset we seeded from uses the newer spelling.
     */
    private const DISTRICT_ALIAS = [
        'chapainawabganj' => 'chapainababganj',
        'jhalakathi' => 'jhalokati',
        'barisal' => 'barishal',
        'netrokona' => 'netrakona',
    ];

    /**
     * Source upazila name (normalised) => our upazila name.
     *
     * "Comilla Sadar" and "Sadarsouth" are the same place under two names in
     * the source; the BBS dataset calls it "Sadar Dakkhin" once, so both rows
     * land on that single upazila.
     */
    private const UPAZILA_ALIAS = [
        'comillasadar' => 'sadar dakkhin',
        'sadarsouth' => 'sadar dakkhin',
        'matlabsouth' => 'matlab dakkhin',
        'matlabnorth' => 'matlab uttar',
        'laxmichhari' => 'lakkhichhari',
        'paikgasa' => 'paikgachha',
        'fultola' => 'phultala',
        'nesarabad' => 'nesarabad (swarupkathi)',
        'phulchari' => 'fulchhari',
        'charrajibpur' => 'rajibpur',
        'barissalsadar' => 'barishal sadar (kotwali)',
    ];

    public function run(): void
    {
        $dir = __DIR__.'/data';
        $districts = $this->readCsv("$dir/bd-districts.csv");
        $upazilas = $this->readCsv("$dir/bd-upazilas.csv");
        $unions = $this->readCsv("$dir/bd-unions.csv");

        if (! $districts || ! $upazilas || ! $unions) {
            $this->command?->warn('Union source CSVs missing; nothing seeded.');

            return;
        }

        $districtNameById = [];
        foreach ($districts as $d) {
            $districtNameById[$d['id']] = $d['name_en'];
        }

        $upazilaMap = $this->mapUpazilas($upazilas, $districtNameById);

        // Every union must hang off a real upazila row or the cascade breaks.
        $created = 0;
        $orphans = [];

        foreach ($unions as $u) {
            $parentId = $upazilaMap[$u['parent_id']] ?? null;

            if ($parentId === null) {
                $orphans[] = $u['name_en'].' (upazila id '.$u['parent_id'].')';

                continue;
            }

            Location::updateOrCreate(
                ['parent_id' => $parentId, 'name' => $u['name_en'], 'type' => 'union'],
                [
                    'name_bn' => $u['name_bn'] ?: null,
                    'code' => null,
                    'sort_order' => 0,
                ]
            );
            $created++;
        }

        $placed = array_filter($upazilaMap, fn ($v) => $v !== null);

        $this->command?->info("Seeded {$created} unions.");
        $this->command?->info(
            'Upazilas matched: '.count($placed).' / '.count($upazilaMap)
        );

        if ($orphans) {
            $this->command?->warn(
                'Unions not attached: '.count($orphans).' ('.implode('; ', array_slice($orphans, 0, 12)).')'
            );
        }
    }

    /** Read a CSV into ['id','parent_id','name_en','name_bn',...]. */
    private function readCsv(string $file): array
    {
        if (! is_file($file)) {
            return [];
        }

        $rows = [];
        $handle = fopen($file, 'r');

        while (($cols = fgetcsv($handle)) !== false) {
            if (count($cols) < 4) {
                continue;
            }
            $rows[] = [
                'id' => (int) $cols[0],
                'parent_id' => (int) $cols[1],
                'name_en' => trim($cols[2]),
                'name_bn' => trim($cols[3]),
            ];
        }
        fclose($handle);

        return $rows;
    }
/**
     * source upazila id => our locations.id for the matching upazila.
     *
     * Null means "could not be placed"; those unions are reported rather than
     * guessed at, because a union under the wrong upazila is worse than none.
     *
     * @return array<int, int|null>
     */
    private function mapUpazilas(array $upazilas, array $districtNameById): array
    {
        $districtRow = [];
        foreach (Location::where('type', 'district')->get() as $d) {
            $districtRow[$this->normalise($d->name)] = $d;
        }

        $byDistrict = [];
        foreach (Location::where('type', 'upazila')->get() as $o) {
            $byDistrict[$o->parent_id][] = $o;
        }

        $map = [];

        foreach ($upazilas as $u) {
            $districtName = $this->aliasDistrict($districtNameById[$u['parent_id']] ?? '');
            $district = $districtRow[$this->normalise($districtName)] ?? null;

            $map[$u['id']] = $district === null
                ? null
                : $this->pick($u['name_en'], $byDistrict[$district->id] ?? []);
        }

        return $map;
    }

    /** Best upazila row for a source name, restricted to one district. */
    private function pick(string $sourceName, array $candidates): ?int
    {
        $target = $this->normalise($this->aliasUpazila($sourceName));

        // 1. identical spelling
        foreach ($candidates as $c) {
            if (strcasecmp($c->name, $sourceName) === 0) {
                return $c->id;
            }
        }

        // 2. identical once punctuation and "Sadar" are dropped
        foreach ($candidates as $c) {
            if ($this->normalise($c->name) === $target) {
                return $c->id;
            }
        }

        // 3. nearest spelling, but only among this district's upazilas
        $best = null;
        $bestDist = 4;

        foreach ($candidates as $c) {
            $distance = levenshtein($target, $this->normalise($c->name));
            if ($distance < $bestDist) {
                $bestDist = $distance;
                $best = $c;
            }
        }

        return $best?->id;
    }

    private function aliasDistrict(string $name): string
    {
        return self::DISTRICT_ALIAS[$this->normalise($name)] ?? $name;
    }

    private function aliasUpazila(string $name): string
    {
        return self::UPAZILA_ALIAS[$this->normalise($name)] ?? $name;
    }

    private function normalise(string $name): string
    {
        return preg_replace('/[^a-z]/', '', strtolower(trim($name)));
    }
}