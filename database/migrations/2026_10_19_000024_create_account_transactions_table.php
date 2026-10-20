<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('txn_number')->unique();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('contra_account_id')->nullable(); // for transfers
            $table->tinyInteger('direction')->comment('1=credit(in), -1=debit(out)');
            $table->tinyInteger('type')->comment('1=opening,2=deposit,3=withdrawal,4=transfer_in,5=transfer_out,6=supplier_payment,7=expense_payment,8=aratdar_receipt,9=owner_capital,10=owner_withdrawal,99=other');
            $table->decimal('amount', 15, 2);
            $table->date('txn_date');
            $table->string('reference')->nullable();       // transaction/cheque/MFS ref
            $table->string('description')->nullable();
            $table->string('reference_type')->nullable();  // polymorphic: purchase, expense, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'txn_date']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_transactions');
    }
};