<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->string('settlement_number')->unique();
            $table->unsignedBigInteger('shipment_id')->unique();
            $table->unsignedBigInteger('aratdar_id');
            $table->date('settlement_date');
            $table->decimal('gross_sales', 15, 2)->default(0);
            $table->decimal('sale_discounts', 15, 2)->default(0);
            $table->decimal('net_sales', 15, 2)->default(0);
            $table->decimal('commission_amount', 15, 2)->default(0);
            $table->decimal('owner_freight', 15, 2)->default(0);
            $table->decimal('aratdar_freight', 15, 2)->default(0);
            $table->decimal('other_deductions', 15, 2)->default(0);
            $table->decimal('expected_net_proceeds', 15, 2)->default(0);
            $table->decimal('remitted_amount', 15, 2)->default(0);
            $table->decimal('outstanding_balance', 15, 2)->default(0);
            $table->decimal('allocated_cogs', 15, 2)->default(0);
            $table->decimal('realized_profit', 15, 2)->default(0);
            $table->tinyInteger('status')->default(SETTLEMENT_STATUS_APPROVED)->comment('0: Pending, 1: Approved');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['aratdar_id', 'settlement_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlements');
    }
};