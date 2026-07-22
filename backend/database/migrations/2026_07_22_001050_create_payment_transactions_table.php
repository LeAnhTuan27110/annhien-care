<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('order_id')->constrained('orders');
            $table->string('gateway');
            $table->string('gateway_transaction_id')->nullable()->index();
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['initiated', 'success', 'failed'])->default('initiated')->index();
            $table->jsonb('raw_response')->nullable();
            $table->timestampTz('processed_at')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};

