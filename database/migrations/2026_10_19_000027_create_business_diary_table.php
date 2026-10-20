<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_diary', function (Blueprint $table) {
            $table->id();
            $table->date('event_date');
            $table->string('title');
            $table->text('notes')->nullable();
            $table->string('event_type')->default('general')
                ->comment('general, purchase, shipment, sale, payment, expense, settlement, reminder, other');
            // Polymorphic link to a related business record (optional)
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['event_date', 'event_type']);
            $table->index(['related_type', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_diary');
    }
};