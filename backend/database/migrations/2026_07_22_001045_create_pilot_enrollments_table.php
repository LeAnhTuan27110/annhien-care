<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pilot_enrollments', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('pilot_program_id')->constrained('pilot_programs');
            $table->foreignId('user_id')->constrained('users');
            $table->timestampTz('enrolled_at')->useCurrent();
            $table->enum('status', ['enrolled', 'active', 'withdrawn'])->default('enrolled');
            $table->timestampsTz();
            $table->unique(['pilot_program_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pilot_enrollments');
    }
};

