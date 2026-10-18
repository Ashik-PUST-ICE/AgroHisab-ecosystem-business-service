<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->unsignedBigInteger('beat_id')->nullable()->after('outlet_id');
            $table->string('verification_status')->nullable()->after('status');
            $table->unsignedInteger('distance_meters')->nullable()->after('longitude');
            $table->unsignedInteger('allowed_radius_meters')->nullable()->after('distance_meters');
            $table->string('outlet_latitude')->nullable()->after('allowed_radius_meters');
            $table->string('outlet_longitude')->nullable()->after('outlet_latitude');
            $table->string('display_condition')->nullable()->after('notes');
            $table->unsignedInteger('display_quantity')->nullable()->after('display_condition');
            $table->index('beat_id');
            $table->index('verification_status');
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropIndex(['beat_id']);
            $table->dropIndex(['verification_status']);
            $table->dropColumn([
                'beat_id',
                'verification_status',
                'distance_meters',
                'allowed_radius_meters',
                'outlet_latitude',
                'outlet_longitude',
                'display_condition',
                'display_quantity',
            ]);
        });
    }
};
