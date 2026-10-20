<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('markets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('district')->nullable();
            $table->string('address')->nullable();
            $table->tinyInteger('type')->default(MARKET_SOURCE)->comment(MARKET_SOURCE.': Source, '.MARKET_DESTINATION.': Destination, '.MARKET_BOTH.': Both');
            $table->text('contact_notes')->nullable();
            $table->tinyInteger('status')->default(STATUS_ACTIVE)->comment(STATUS_ACTIVE.': Active, '.STATUS_INACTIVE.': Inactive');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('markets');
    }
};
