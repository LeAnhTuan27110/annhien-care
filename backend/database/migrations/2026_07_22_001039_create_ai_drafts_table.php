<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_drafts', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('patient_id')->index()->constrained('users');
            $table->enum('draft_type', ['record_explanation', 'alert_summary', 'care_plan', 'weekly_report']);
            $table->jsonb('source_data');
            $table->text('ai_generated_text');
            $table->string('model_used');
            $table->timestampTz('generated_at')->useCurrent();
            $table->text('edited_text')->nullable();
            $table->enum('status', ['pending', 'edited', 'approved', 'rejected'])->default('pending')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestampTz('reviewed_at')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_drafts');
    }
};

