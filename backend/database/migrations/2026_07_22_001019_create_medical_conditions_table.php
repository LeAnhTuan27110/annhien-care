<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_conditions', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('patient_id')->index()->constrained('users');
            $table->string('condition_name');
            $table->string('icd_code')->nullable();
            $table->date('diagnosed_date')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'resolved', 'chronic'])->default('active');
            $table->enum('source', ['manual', 'ocr', 'ai_draft'])->default('manual');
            $this->verificationColumns($table);
            $table->foreignId('created_by')->constrained('users');
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    private function verificationColumns(Blueprint $table): void
    {
        $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending')->index();
        $table->foreignId('verified_by')->nullable()->constrained('users');
        $table->timestampTz('verified_at')->nullable();
        $table->text('rejection_reason')->nullable();
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_conditions');
    }
};

