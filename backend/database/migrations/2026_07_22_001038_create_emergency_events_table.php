<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_events', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('patient_id')->index()->constrained('users');
            $table->foreignId('triggered_by')->constrained('users');
            $table->timestampTz('triggered_at')->useCurrent();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->jsonb('contacts_notified');
            $table->boolean('disclaimer_shown')->default(true);
            $table->enum('status', ['initiated', 'contacted', 'resolved'])->default('initiated')->index();
            $table->timestampTz('resolved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_events');
    }
};

