<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('price', 12, 2);
            $table->enum('billing_cycle', ['monthly', 'yearly']);
            $table->jsonb('features');
            $table->boolean('is_active')->default(true)->index();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};

