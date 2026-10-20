<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_number')->unique();
            $table->unsignedBigInteger('shipment_id');
            $table->unsignedBigInteger('shipment_item_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->date('sale_date');
            $table->decimal('quantity', 12, 4);
            $table->decimal('unit_rate', 15, 2);
            $table->decimal('gross_amount', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2);
            $table->tinyInteger('commission_type')->default(COMMISSION_TYPE_PERCENTAGE)->comment('1: Percentage, 2: Fixed');
            $table->decimal('commission_rate', 10, 4)->default(0);
            $table->decimal('commission_amount', 15, 2)->default(0);
            $table->decimal('net_proceeds', 15, 2)->default(0);
            $table->string('buyer_name')->nullable();
            $table->string('buyer_phone')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['shipment_id', 'product_id']);
            $table->index('sale_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_sales');
    }
};