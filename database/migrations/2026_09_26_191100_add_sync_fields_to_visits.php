<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->string('sync_status')->default('synced')->after('client_id');
            $table->string('device_info')->nullable()->after('sync_status');
            $table->index('sync_status');
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropIndex(['sync_status']);
            $table->dropColumn(['sync_status', 'device_info']);
        });
    }
};
