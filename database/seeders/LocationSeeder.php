<?php

namespace Database\Seeders;

use App\Models\Business\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Imports division -> district -> upazila (8 / 64 / 495 records).
     *
     * Source: Open Admin Data "Bangladesh Administrative Divisions", CC-BY-4.0
     * https://github.com/open-admin-data/bangladesh-administrative-divisions
     *
     * The JSON is vendored into database/seeders/data so seeding never needs
     * the network. Union / ward / village are not published in this dataset;
     * those levels are added from inside the app and stay empty until then.
     */
    private const DATA_FILE = __DIR__.'/data/bd-administrative-divisions.json';

    public function run(): void
    {
        $path = self::DATA_FILE;

        if (! is_file($path)) {
            $this->command?->warn("Location data file missing at {$path}; nothing seeded.");

            return;
        }

        $payload = json_decode(file_get_contents($path), true);
        $rows = $payload['data'] ?? [];

        if (! $rows) {
            $this->command?->warn('Location data file contained no records.');

            return;
        }

        // Official code -> our row id, so a child's parent resolves in one pass.
        $idsByCode = [];
        $counts = ['division' => 0, 'district' => 0, 'upazila' => 0];

        foreach ($rows as $row) {
            $type = $this->typeForLevel($row['level'] ?? null);

            if ($type === null) {
                continue;
            }

            $code = $row['id'] ?? null;
            $parentId = null;

            if (! empty($row['parent']['id'])) {
                $parentId = $idsByCode[$row['parent']['id']] ?? null;
            }

            $location = $this->upsert($parentId, $row, $type);

            if ($code !== null) {
                $idsByCode[$code] = $location->id;
            }

            $counts[$type]++;
        }

        $this->command?->info(sprintf(
            'Seeded %d divisions, %d districts, %d upazilas.',
            $counts['division'],
            $counts['district'],
            $counts['upazila']
        ));
    }

    private function typeForLevel(?int $level): ?string
    {
        return match ($level) {
            1 => 'division',
            2 => 'district',
            3 => 'upazila',
            default => null,
        };
    }

    /**
     * Keyed on the official code so re-running updates rows in place. Records
     * added by hand have a null code and are matched on parent + name instead.
     */
    private function upsert(?int $parentId, array $row, string $type): Location
    {
        $code = $row['id'] ?? null;
        $name = trim((string) ($row['name']['en'] ?? ''));

        $search = $code !== null
            ? ['code' => $code]
            : ['parent_id' => $parentId, 'name' => $name, 'type' => $type];

        return Location::updateOrCreate($search, [
            'parent_id' => $parentId,
            'name' => $name,
            'name_bn' => $row['name']['local'] ?? null,
            'type' => $type,
            'lat' => isset($row['geo']['lat']) ? (float) $row['geo']['lat'] : null,
            'lng' => isset($row['geo']['lon']) ? (float) $row['geo']['lon'] : null,
            'sort_order' => 0,
        ]);
    }
}