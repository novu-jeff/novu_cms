<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_signing_audits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('signing_request_id')
                ->constrained('document_signing_requests')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('event', 50);
            $table->text('description')->nullable();

            $table->json('metadata')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['signing_request_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_signing_audits');
    }
};
