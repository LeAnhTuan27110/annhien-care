<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('patient_id')->index()->constrained('users');
            $table->enum('report_type', ['daily', 'weekly']);
            $table->date('period_start');
            $table->date('period_end');
            $table->timestampTz('generated_at')->useCurrent();
            $table->string('file_url')->nullable();
            $table->jsonb('summary_json');
            $table->enum('status', ['generated', 'viewed', 'responded'])->default('generated')->index();
            $table->timestampsTz();
            $table->unique(['patient_id', 'report_type', 'period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};

