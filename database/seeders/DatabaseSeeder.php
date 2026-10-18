<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * The location chain has to run in this order: a district has to exist
     * before its upazila, an upazila before its union, a union before its
     * ward. Each seeder is idempotent, so re-running is safe.
     */
    public function run(): void
    {
        $this->call([
            LocationSeeder::class,
            UnionSeeder::class,
            PourashavaSeeder::class,
            WardSeeder::class,
        ]);
    }
}
