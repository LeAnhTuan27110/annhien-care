<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caregiver_profiles', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->unique()->constrained('users');
            $table->enum('caregiver_type', ['nurse', 'volunteer', 'personal_caregiver']);
            $table->string('license_number')->nullable();
            $table->enum('license_verified_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->foreignId('license_verified_by')->nullable()->constrained('users');
            $table->timestampTz('license_verified_at')->nullable();
            $table->smallInteger('years_experience')->nullable();
            $table->text('bio')->nullable();
            $table->jsonb('skills')->nullable();
            $table->decimal('hourly_rate', 12, 2)->nullable();
            $table->smallInteger('service_radius_km')->nullable();
            $table->decimal('base_latitude', 10, 7)->nullable();
            $table->decimal('base_longitude', 10, 7)->nullable();
            $table->enum('background_check_status', ['pending', 'cleared', 'flagged'])->default('pending');
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caregiver_profiles');
    }
};

