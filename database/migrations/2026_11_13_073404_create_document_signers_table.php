<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_signers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('signing_request_id')
                ->constrained('document_signing_requests')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Examples: secretary, presiding_officer,
            // committee_chairperson.
            $table->string('signer_role', 100);

            $table->unsignedInteger('signing_order');

            $table->enum('status', [
                'pending',
                'signed',
                'rejected',
            ])->default('pending');

            $table->timestamp('signed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->unique(
                ['signing_request_id', 'signing_order'],
                'ds_signing_order_unique'
            );

            $table->unique(
                ['signing_request_id', 'user_id'],
                'ds_signer_unique'
            );

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_signers');
    }
};
