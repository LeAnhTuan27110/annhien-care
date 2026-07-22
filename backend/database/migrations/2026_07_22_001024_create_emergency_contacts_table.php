<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_contacts', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('patient_id')->index()->constrained('users');
            $table->string('name');
            $table->string('relationship');
            $table->string('phone');
            $table->smallInteger('priority_order');
            $table->timestampsTz();
            $table->unique(['patient_id', 'priority_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_contacts');
    }
};

