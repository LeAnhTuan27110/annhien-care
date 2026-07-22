<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_results', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('patient_id')->index()->constrained('users');
            $table->string('test_name');
            $table->string('result_value');
            $table->string('unit')->nullable();
            $table->string('reference_range')->nullable();
            $table->date('test_date');
            $table->string('file_url')->nullable();
            $table->enum('source', ['manual', 'ocr'])->default('manual');
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
        Schema::dropIfExists('lab_results');
    }
};

