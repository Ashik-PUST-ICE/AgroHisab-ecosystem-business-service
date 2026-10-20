<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aratdars', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('business_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('market')->nullable();
            $table->string('district')->nullable();
            $table->text('notes')->nullable();
            $table->date('agreement_start')->nullable();
            $table->date('agreement_end')->nullable();
            $table->tinyInteger('commission_type')->default(1)->comment('1: Percentage, 2: Fixed');
            $table->decimal('commission_value', 10, 4)->default(0);
            $table->string('commission_base')->nullable()->comment('gross_sales or net_sales');
            $table->tinyInteger('status')->default(1)->comment('1: Active, 0: Inactive');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aratdars');
    }
};
