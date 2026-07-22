<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('care_schedules', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('patient_id')->index()->constrained('users');
            $table->foreignId('source_order_id')->nullable()->constrained('medical_orders');
            $table->enum('type', ['medication', 'vital_check', 'follow_up', 'task']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('recurrence_rule')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'paused', 'ended'])->default('active');
            $table->foreignId('created_by')->constrained('users');
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('care_schedules');
    }
};

