<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('assigned_user_id')->nullable();
            $table->string('name');
            $table->string('code')->nullable();
            $table->date('date')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();

            $table->index('company_id');
            $table->index('assigned_user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beats');
    }
};
