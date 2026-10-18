<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the Bangladesh administrative hierarchy to `outlets`, so a field officer
 * can identify a shop locationally (not just by GPS):
 *
 *   Rural: division -> district -> upazila -> union -> ward -> village
 *   Urban: division -> district -> upazila -> pourashava -> ward -> mahalla
 *
 * Union and pourashava are alternatives at the same depth (rural vs town),
 * so an outlet fills one or the other, never both.
 *
 * Deliberately mirrors the standard Bangla address order; "upazila" (not
 * "thana") and "village" (not "gram") are the correct English terms.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->string('division')->nullable()->after('address');
            $table->string('district')->nullable()->after('division');
            $table->string('upazila')->nullable()->after('district');
            $table->string('union')->nullable()->after('upazila');
            // Urban counterpart of union (town chain); one or the other is set.
            $table->string('pourashava')->nullable()->after('union');
            $table->string('ward')->nullable()->after('union');
            $table->string('village')->nullable()->after('ward');

            // Compound index for "show me outlets in X district" style filters.
            $table->index(['district', 'upazila'], 'outlets_district_upazila_index');
        });
    }

    public function down(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->dropIndex('outlets_district_upazila_index');
            $table->dropColumn([
                'division', 'district', 'upazila', 'union', 'pourashava', 'ward', 'village',
            ]);
        });
    }
};