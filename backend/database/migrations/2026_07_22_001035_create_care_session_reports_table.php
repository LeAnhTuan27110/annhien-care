<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('care_session_reports', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('care_session_id')->unique()->constrained('care_sessions');
            $table->text('patient_condition_summary');
            $table->text('tasks_performed');
            $table->jsonb('medication_given')->nullable();
            $table->text('observations')->nullable();
            $table->boolean('family_notified')->default(false);
            $table->boolean('doctor_notified')->default(false);
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('care_session_reports');
    }
};

