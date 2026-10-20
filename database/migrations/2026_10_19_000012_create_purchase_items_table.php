<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('unit_id')->nullable()->constrained('units');
            $table->integer('bag_count')->default(0);
            $table->decimal('actual_weight', 12, 4)->default(0)->comment('Actual raw weight weighed (কাঁচা ওজন, e.g. 46kg)');
            $table->decimal('deducted_weight', 12, 4)->default(0)->comment('Deduction/cut (কাটতি / ধলতা / বাদ দেওয়া ওজন, e.g. 1kg)');
            $table->decimal('gross_weight', 12, 4)->default(0);
            $table->decimal('tare_weight', 12, 4)->default(0);
            $table->decimal('net_weight', 12, 4)->default(0)->comment('Net payable weight (হিসাবি ওজন, e.g. 45kg)');
            $table->decimal('mon_rate', 15, 2)->nullable()->comment('Rate per maund (মণের দর, e.g. 122 tk)');
            $table->decimal('mon_size', 8, 2)->default(40.00)->comment('Maund size in kg (e.g. 40kg)');
            $table->decimal('unit_rate', 15, 2)->default(0)->comment('Rate per kg');
            $table->decimal('line_total', 15, 2)->default(0)->comment('Total payable to farmer');
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};