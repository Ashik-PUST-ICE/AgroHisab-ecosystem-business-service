<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('actor_id')->nullable();
            $table->string('target_id')->nullable();
            $table->string('category')->nullable();
            $table->string('action')->nullable();
            $table->string('message');
            $table->json('context')->nullable();
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('level')->default('info');
            $table->string('request_id')->nullable();
            $table->string('trace_id')->nullable();
            $table->timestamps();

            $table->index('actor_id');
            $table->index('target_id');
            $table->index('category');
            $table->index('action');
            $table->index('request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
