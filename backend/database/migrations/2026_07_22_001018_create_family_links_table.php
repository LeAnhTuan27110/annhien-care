<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_links', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('patient_id')->constrained('users');
            $table->foreignId('family_user_id')->constrained('users');
            $table->string('relationship_type');
            $table->enum('permission_level', ['full', 'view_only', 'emergency_only']);
            $table->string('consent_document_url')->nullable();
            $table->timestampTz('consented_at')->nullable();
            $table->enum('status', ['pending', 'active', 'revoked'])->default('pending');
            $table->foreignId('created_by')->constrained('users');
            $table->timestampsTz();
            $table->unique(['patient_id', 'family_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_links');
    }
};

