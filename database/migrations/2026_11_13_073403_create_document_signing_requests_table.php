<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_signing_requests', function (Blueprint $table) {
            $table->id();

            // Identifies the originating module and record.
            $table->string('document_type', 50);
            $table->unsignedBigInteger('document_id');

            $table->string('reference_no')->nullable();
            $table->string('document_title');

            // Frozen PDF submitted for signing.
            $table->string('document_path');
            $table->char('document_hash', 64);

            // Final PDF after all required signatures.
            $table->string('signed_document_path')->nullable();
            $table->char('signed_document_hash', 64)->nullable();

            $table->enum('status', [
                'draft',
                'pending',
                'partially_signed',
                'fully_signed',
                'rejected',
                'revoked',
            ])->default('draft');

            $table->foreignId('initiated_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('revoked_at')->nullable();

            $table->timestamps();

            $table->index(
                ['document_type', 'document_id'],
                'dsr_document_lookup_idx'
            );

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_signing_requests');
    }
};
