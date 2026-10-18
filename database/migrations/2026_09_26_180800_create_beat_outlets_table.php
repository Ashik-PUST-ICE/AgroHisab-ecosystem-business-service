<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beat_outlets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('beat_id')->nullable();
            $table->unsignedBigInteger('outlet_id')->nullable();
            $table->unsignedInteger('sequence')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->index('beat_id');
            $table->index('outlet_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beat_outlets');
    }
};
