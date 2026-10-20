<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases');
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('account_id')->constrained('accounts');
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['purchase_id', 'supplier_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
    }
};