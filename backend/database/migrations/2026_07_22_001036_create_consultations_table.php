<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('patient_id')->index()->constrained('users');
            $table->foreignId('requester_id')->constrained('users');
            $table->foreignId('provider_id')->constrained('users');
            $table->enum('consultation_type', ['chat', 'video']);
            $table->enum('status', ['requested', 'accepted', 'in_progress', 'completed', 'cancelled'])->default('requested')->index();
            $table->timestampTz('scheduled_at')->nullable();
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('ended_at')->nullable();
            $table->jsonb('structured_record_snapshot')->nullable();
            $table->text('summary_notes')->nullable();
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending')->index();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestampTz('verified_at')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};

