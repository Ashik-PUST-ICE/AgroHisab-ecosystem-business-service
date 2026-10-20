<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aratdar_remittances', function (Blueprint $table) {
            $table->id();
            $table->string('remittance_number')->unique();
            $table->unsignedBigInteger('aratdar_id');
            $table->unsignedBigInteger('account_id');
            $table->decimal('amount', 15, 2);
            $table->decimal('allocated_amount', 15, 2)->default(0);
            $table->decimal('unallocated_amount', 15, 2)->default(0);
            $table->date('payment_date');
            $table->string('payment_method')->default('bank')->comment('cash, bank, bkash, nagad, rocket, cheque, other');
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['aratdar_id', 'payment_date']);
            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aratdar_remittances');
    }
};