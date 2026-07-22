<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('care_sessions', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('booking_id')->constrained('bookings')->unique();
            $table->foreignId('caregiver_id')->constrained('users');
            $table->foreignId('patient_id')->index()->constrained('users');
            $table->timestampTz('check_in_at')->nullable();
            $table->decimal('check_in_latitude', 10, 7)->nullable();
            $table->decimal('check_in_longitude', 10, 7)->nullable();
            $table->timestampTz('check_out_at')->nullable();
            $table->decimal('check_out_latitude', 10, 7)->nullable();
            $table->decimal('check_out_longitude', 10, 7)->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started')->index();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('care_sessions');
    }
};

