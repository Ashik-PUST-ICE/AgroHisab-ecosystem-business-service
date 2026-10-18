<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('title')->nullable();
            $table->string('period_type', 30)->default('daily');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedInteger('visit_target')->default(0);
            $table->decimal('order_amount_target', 12, 2)->default(0);
            $table->unsignedInteger('order_count_target')->default(0);
            $table->decimal('coverage_target_percentage', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->index('company_id');
            $table->index('user_id');
            $table->index('period_type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_targets');
    }
};
