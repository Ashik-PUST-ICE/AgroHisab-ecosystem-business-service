<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_number')->unique();
            $table->date('purchase_date');
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('market_id')->nullable()->constrained('markets');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('due_amount', 15, 2)->default(0);
            $table->tinyInteger('status')->default(PURCHASE_STATUS_DRAFT)->comment(PURCHASE_STATUS_DRAFT.': Draft, '.PURCHASE_STATUS_CONFIRMED.': Confirmed, '.PURCHASE_STATUS_RECEIVED.': Received, '.PURCHASE_STATUS_CANCELLED.': Cancelled');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};