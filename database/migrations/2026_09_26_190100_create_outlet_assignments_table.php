<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outlet_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('outlet_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();

            $table->index('company_id');
            $table->index('outlet_id');
            $table->index('user_id');
            $table->unique(['outlet_id', 'user_id'], 'outlet_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outlet_assignments');
    }
};
