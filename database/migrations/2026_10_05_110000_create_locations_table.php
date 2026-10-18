<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('locations')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('name_bn', 120)->nullable();
            $table->string('type', 20);

            // Official Bangladesh Bureau of Statistics code (BD10, BD1004 ...).
            // Null for levels added by hand (union / ward / village).
            $table->string('code', 20)->nullable();

            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['type', 'parent_id']);
            $table->unique('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};