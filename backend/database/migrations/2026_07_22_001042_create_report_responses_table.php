<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_responses', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('report_id')->constrained('reports');
            $table->foreignId('responded_by')->constrained('users');
            $table->text('response_text');
            $table->jsonb('order_adjustments')->nullable();
            $table->timestampTz('responded_at')->useCurrent();
            $table->timestampTz('sent_to_user_at')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_responses');
    }
};

