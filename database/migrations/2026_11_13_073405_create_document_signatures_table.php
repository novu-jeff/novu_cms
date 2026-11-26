<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_signatures', function (Blueprint $table) {
            $table->id();

            $table->foreignId('document_signer_id')
                ->constrained('document_signers')
                ->cascadeOnDelete();

            $table->enum('signature_type', [
                'electronic',
                'certificate',
            ])->default('electronic');

            // Optional signature image, stored privately.
            $table->string('signature_path')->nullable();

            // Hash of the frozen document that was signed.
            $table->char('document_hash', 64);

            // Hash of the actual signature image, if applicable.
            $table->char('signature_hash', 64)->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Future certificate-based signing fields.
            $table->string('certificate_serial')->nullable();
            $table->string('certificate_issuer')->nullable();

            $table->timestamp('signed_at');

            $table->timestamps();

            $table->unique('document_signer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_signatures');
    }
};
