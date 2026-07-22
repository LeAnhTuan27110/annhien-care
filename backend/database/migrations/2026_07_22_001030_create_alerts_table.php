<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('patient_id')->index()->constrained('users');
            $table->foreignId('alert_rule_id')->constrained('alert_rules');
            $table->foreignId('symptom_log_id')->nullable()->constrained('symptom_logs');
            $table->timestampTz('triggered_at')->useCurrent();
            $table->enum('severity', ['low', 'medium', 'red']);
            $table->text('message');
            $table->enum('status', ['new', 'acknowledged', 'resolved'])->default('new')->index();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users');
            $table->timestampTz('acknowledged_at')->nullable();
            $table->timestampTz('resolved_at')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};

