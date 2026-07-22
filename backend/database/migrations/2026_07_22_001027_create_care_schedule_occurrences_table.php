<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('care_schedule_occurrences', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('care_schedule_id')->constrained('care_schedules');
            $table->timestampTz('scheduled_at');
            $table->enum('status', ['pending', 'completed', 'missed', 'skipped'])->default('pending')->index();
            $table->timestampTz('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->timestampsTz();
            $table->unique(['care_schedule_id', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('care_schedule_occurrences');
    }
};

