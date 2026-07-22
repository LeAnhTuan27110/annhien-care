<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->index()->constrained('users');
            $table->foreignId('plan_id')->constrained('subscription_plans');
            $table->enum('status', ['active', 'cancelled', 'expired', 'past_due'])->default('active')->index();
            $table->timestampTz('started_at');
            $table->timestampTz('current_period_end');
            $table->timestampTz('cancelled_at')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};

