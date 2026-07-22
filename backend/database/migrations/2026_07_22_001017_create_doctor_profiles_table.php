<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_profiles', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->unique()->constrained('users');
            $table->string('license_number');
            $table->string('specialty');
            $table->string('hospital_affiliation')->nullable();
            $table->enum('license_verified_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->foreignId('license_verified_by')->nullable()->constrained('users');
            $table->timestampTz('license_verified_at')->nullable();
            $table->decimal('consultation_fee', 12, 2)->nullable();
            $table->boolean('can_author_alert_rules')->default(false);
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_profiles');
    }
};

