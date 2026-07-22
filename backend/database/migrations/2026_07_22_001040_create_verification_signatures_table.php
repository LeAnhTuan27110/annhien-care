<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_signatures', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->morphs('verifiable');
            $table->foreignId('verified_by')->constrained('users');
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->string('signature_hash');
            $table->timestampTz('signed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('device_info')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_signatures');
    }
};

