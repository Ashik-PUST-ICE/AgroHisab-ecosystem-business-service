<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('market_id')->nullable()->constrained('markets');
            $table->tinyInteger('type')->comment(STOCK_PURCHASE.': Purchase, '.STOCK_DISPATCH.': Dispatch, '.STOCK_RECEIVE.': Receive, '.STOCK_SALE.': Sale, '.STOCK_RETURN.': Return, '.STOCK_DAMAGE.': Damage, '.STOCK_ADJUSTMENT.': Adjustment');
            $table->decimal('quantity', 12, 4);
            $table->foreignId('unit_id')->nullable()->constrained('units');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->unsignedBigInteger('reference_item_id')->nullable();
            $table->string('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};