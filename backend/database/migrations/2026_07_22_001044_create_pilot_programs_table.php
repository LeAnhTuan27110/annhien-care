<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pilot_programs', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('region');
            $table->string('partner_group')->nullable();
            $table->unsignedInteger('max_participants');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['planned', 'active', 'closed'])->default('planned')->index();
            $table->foreignId('created_by')->constrained('users');
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pilot_programs');
    }
};

