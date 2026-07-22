<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_events', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->nullable()->index()->constrained('users');
            $table->enum('event_type', ['login_success', 'login_failed', 'mfa_challenge', 'password_reset', 'suspicious_access', 'account_locked']);
            $table->string('ip_address', 45);
            $table->string('device_info')->nullable();
            $table->timestampTz('occurred_at')->useCurrent()->index();
            $table->smallInteger('risk_score')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_events');
    }
};

