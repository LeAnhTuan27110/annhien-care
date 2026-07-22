<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('patient_id')->index()->constrained('users');
            $table->foreignId('requested_by')->constrained('users');
            $table->string('service_type');
            $table->text('description');
            $table->text('scope_of_work')->nullable();
            $table->timestampTz('preferred_start');
            $table->timestampTz('preferred_end')->nullable();
            $table->string('location_address');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->enum('status', ['open', 'matched', 'confirmed', 'in_progress', 'completed', 'cancelled'])->default('open')->index();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};

