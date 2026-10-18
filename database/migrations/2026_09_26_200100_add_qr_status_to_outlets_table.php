<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->string('qr_status')->default('active')->after('qr_token');
            $table->timestamp('qr_generated_at')->nullable()->after('qr_status');
            $table->timestamp('qr_deactivated_at')->nullable()->after('qr_generated_at');
        });
    }

    public function down(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->dropColumn(['qr_status', 'qr_generated_at', 'qr_deactivated_at']);
        });
    }
};
