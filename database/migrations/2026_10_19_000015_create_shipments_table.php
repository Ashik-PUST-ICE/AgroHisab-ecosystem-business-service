<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_number')->unique();
            $table->unsignedBigInteger('aratdar_id')->nullable();
            $table->unsignedBigInteger('transporter_id')->nullable();
            $table->unsignedBigInteger('market_id')->nullable()->comment('Destination market');
            $table->date('dispatch_date')->nullable();
            $table->date('expected_arrival_date')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->decimal('freight_amount', 15, 2)->default(0);
            $table->string('freight_paid_by')->default('owner')->comment('owner, aratdar, shared');
            $table->json('expense_breakdown')->nullable()->comment('Dynamic itemized expense list: head, amount, paid_by, notes');
            $table->tinyInteger('status')->default(SHIPMENT_STATUS_DRAFT)->comment(SHIPMENT_STATUS_DRAFT.': Draft, '.SHIPMENT_STATUS_DISPATCHED.': Dispatched, '.SHIPMENT_STATUS_RECEIVED.': Received, '.SHIPMENT_STATUS_SELLING.': Selling, '.SHIPMENT_STATUS_SETTLED.': Settled');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};