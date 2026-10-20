<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number', 50)->unique();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('market_id')->nullable()->constrained('markets');
            $table->tinyInteger('loss_type')->comment('1: Spoilage, 2: Weight Loss, 3: Damage, 4: Sorting Waste, 5: Shortage, 6: Count Reconciliation');
            $table->decimal('quantity', 12, 4);
            $table->foreignId('unit_id')->constrained('units');
            $table->date('adjustment_date');
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'adjustment_date']);
            $table->index('loss_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};