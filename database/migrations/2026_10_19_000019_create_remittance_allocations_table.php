<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('remittance_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('aratdar_remittance_id');
            $table->unsignedBigInteger('shipment_id');
            $table->decimal('amount', 15, 2);
            $table->date('allocation_date');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['aratdar_remittance_id', 'shipment_id']);
            $table->index('shipment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remittance_allocations');
    }
};