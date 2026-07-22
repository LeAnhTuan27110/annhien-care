<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caregiver_availability', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('caregiver_id')->index()->constrained('users');
            $table->unsignedTinyInteger('day_of_week')->nullable();
            $table->date('specific_date')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_recurring');
            $table->timestampsTz();
            $table->index(['caregiver_id', 'specific_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caregiver_availability');
    }
};

