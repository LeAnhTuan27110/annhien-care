<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_rules', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('metric_type');
            $table->enum('condition_operator', ['>', '>=', '<', '<=', '=']);
            $table->decimal('threshold_value', 10, 2);
            $table->enum('severity', ['low', 'medium', 'red']);
            $table->enum('scope', ['global', 'patient_specific']);
            $table->foreignId('patient_id')->nullable()->index()->constrained('users');
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->constrained('users');
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_rules');
    }
};

