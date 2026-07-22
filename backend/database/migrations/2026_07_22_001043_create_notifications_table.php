<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained('users');
            $table->string('type');
            $table->string('title');
            $table->text('body');
            $table->enum('channel', ['push', 'sms', 'call', 'email', 'in_app']);
            $table->nullableMorphs('related');
            $table->enum('status', ['queued', 'sent', 'delivered', 'failed', 'read'])->default('queued');
            $table->timestampTz('sent_at')->nullable();
            $table->timestampTz('read_at')->nullable();
            $table->timestampsTz();
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

