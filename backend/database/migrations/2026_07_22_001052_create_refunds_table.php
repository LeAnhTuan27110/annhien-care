<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('order_id')->constrained('orders');
            $table->decimal('amount', 12, 2);
            $table->text('reason');
            $table->enum('status', ['requested', 'approved', 'rejected', 'processed'])->default('requested')->index();
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('processed_by')->nullable()->constrained('users');
            $table->timestampTz('processed_at')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};

