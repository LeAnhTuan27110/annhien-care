<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('service_request_id')->constrained('service_requests');
            $table->foreignId('caregiver_id')->index()->constrained('users');
            $table->foreignId('patient_id')->index()->constrained('users');
            $table->timestampTz('scheduled_start');
            $table->timestampTz('scheduled_end');
            $table->enum('status', ['pending_confirmation', 'confirmed', 'declined', 'cancelled', 'completed'])->default('pending_confirmation')->index();
            $table->timestampTz('confirmed_at')->nullable();
            $table->decimal('price', 12, 2);
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

