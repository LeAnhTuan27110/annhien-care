<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('symptom_logs', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('patient_id')->index()->constrained('users');
            $table->foreignId('logged_by')->constrained('users');
            $table->date('log_date')->index();
            $table->smallInteger('pain_level')->nullable();
            $table->decimal('temperature_celsius', 4, 1)->nullable();
            $table->boolean('breathing_difficulty')->default(false);
            $table->enum('appetite', ['normal', 'reduced', 'none'])->nullable();
            $table->enum('sleep_quality', ['good', 'fair', 'poor'])->nullable();
            $table->enum('mobility', ['normal', 'limited', 'bedridden'])->nullable();
            $table->enum('mood', ['good', 'neutral', 'low'])->nullable();
            $table->jsonb('vitals')->nullable();
            $table->text('notes')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->index(['patient_id', 'log_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('symptom_logs');
    }
};
