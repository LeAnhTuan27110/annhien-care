<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_orders', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('patient_id')->index()->constrained('users');
            $table->foreignId('doctor_id')->constrained('users');
            $table->enum('order_type', ['medication', 'test', 'care_plan', 'follow_up']);
            $table->text('description');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $this->verificationColumns($table);
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
        Schema::dropIfExists('medical_orders');
    }
};

