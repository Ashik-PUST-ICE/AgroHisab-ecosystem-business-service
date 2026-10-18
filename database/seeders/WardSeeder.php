<?php

namespace Database\Seeders;

use App\Models\Business\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Generates the wards of every union and pourashava.
 *
 * A Union Parishad area is divided into nine numbered wards by law, and a
 * pourashava is likewise divided into wards, so these are structural rows
 * rather than names pulled from a gazetteer - Ward 1 to Ward 9 under each
 * parent. They exist so the outlet form offers a picker instead of a free
 * text box; where a place genuinely has a different number, an officer can
 * still type a value, and rows added by hand are left alone.
 *
 * Village is deliberately not generated. Villages have real names and no
 * national open dataset for them, so inventing some would be worse than none.
 * (Under a pourashava the same tail level holds mahallas, entered by hand.)
 */
class WardSeeder extends Seeder
{
    private const WARDS_PER_UNION = 9;

    private const BATCH = 500;

    public function run(): void
    {
        // Wards hang off unions (rural) and pourashavas (urban) alike.
        $parentIds = Location::whereIn('type', ['union', 'pourashava'])->pluck('id');

        if ($parentIds->isEmpty()) {
            $this->command?->warn('No unions found. Run UnionSeeder first.');

            return;
        }

        // Drop the previously generated rows first so the seeder can be re-run.
        // The Bangla marker is only ever set by this seeder, so a ward typed in
        // by hand from the app survives.
        $removed = DB::table('locations')
            ->where('type', 'ward')
            ->where('name_bn', 'like', 'ওয়ার্ড %')
            ->delete();

        if ($removed > 0) {
            $this->command?->info("Removed {$removed} previously generated wards.");
        }

        $created = 0;
        $buffer = [];

        foreach ($parentIds as $parentId) {
            for ($n = 1; $n <= self::WARDS_PER_UNION; $n++) {
                $buffer[] = [
                    'parent_id' => $parentId,
                    'name' => 'Ward '.$n,
                    'name_bn' => 'ওয়ার্ড '.$this->toBanglaDigits($n),
                    'type' => 'ward',
                    'code' => null,
                    'lat' => null,
                    'lng' => null,
                    'sort_order' => $n,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($buffer) >= self::BATCH) {
                    DB::table('locations')->insert($buffer);
                    $created += count($buffer);
                    $buffer = [];
                }
            }
        }

        if ($buffer) {
            DB::table('locations')->insert($buffer);
            $created += count($buffer);
        }

        $this->command?->info(
            "Seeded {$created} wards (".self::WARDS_PER_UNION.' per parent across '.$parentIds->count().' unions/pourashavas).'
        );
    }

    private function toBanglaDigits(int $n): string
    {
        return strtr((string) $n, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯']);
    }
}