<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipment_id')->nullable();
            $table->unsignedBigInteger('purchase_item_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->integer('bag_count')->default(0);
            $table->decimal('dispatched_weight', 12, 4)->default(0);
            $table->decimal('received_weight', 12, 4)->default(0)->comment('Filled on goods receipt');
            $table->decimal('shortage_weight', 12, 4)->default(0)->comment('dispatched - received');
            $table->decimal('unit_rate', 15, 2)->default(0)->comment('Purchase unit rate for COGS');
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_items');
    }
};