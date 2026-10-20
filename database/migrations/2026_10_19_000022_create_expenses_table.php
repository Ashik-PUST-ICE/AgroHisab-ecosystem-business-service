<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique();
            $table->unsignedBigInteger('expense_category_id');
            $table->unsignedBigInteger('shipment_id')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('title');
            $table->decimal('amount', 15, 2);
            $table->date('expense_date');
            $table->string('paid_by')->default('owner')->comment('owner, aratdar, shared');
            $table->tinyInteger('payment_status')->default(EXPENSE_PAYMENT_STATUS_PAID);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['expense_category_id', 'expense_date']);
            $table->index('shipment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};