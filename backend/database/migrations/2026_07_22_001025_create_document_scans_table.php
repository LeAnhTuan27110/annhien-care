<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_scans', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('patient_id')->index()->constrained('users');
            $table->foreignId('uploaded_by')->constrained('users');
            $table->string('file_url');
            $table->enum('file_type', ['image', 'pdf']);
            $table->enum('ocr_status', ['processing', 'done', 'failed'])->default('processing');
            $table->text('ocr_raw_text')->nullable();
            $table->decimal('ocr_confidence', 5, 2)->nullable();
            $table->nullableMorphs('linked_record');
            $table->enum('review_status', ['pending', 'edited', 'confirmed', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestampTz('reviewed_at')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_scans');
    }
};

