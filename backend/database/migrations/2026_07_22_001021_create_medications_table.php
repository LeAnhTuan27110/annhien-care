<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medications', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('patient_id')->index()->constrained('users');
            $table->string('drug_name');
            $table->string('dosage');
            $table->string('frequency');
            $table->string('route')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->foreignId('prescribing_doctor_id')->nullable()->constrained('users');
            $table->text('instructions')->nullable();
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
        Schema::dropIfExists('medications');
    }
};

