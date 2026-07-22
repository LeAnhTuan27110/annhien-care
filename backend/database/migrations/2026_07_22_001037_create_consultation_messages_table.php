<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_messages', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('consultation_id')->constrained('consultations');
            $table->foreignId('sender_id')->constrained('users');
            $table->enum('message_type', ['text', 'file', 'image']);
            $table->text('content');
            $table->timestampTz('sent_at')->useCurrent();
            $table->timestampTz('read_at')->nullable();
            $table->timestampsTz();
            $table->index(['consultation_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_messages');
    }
};

