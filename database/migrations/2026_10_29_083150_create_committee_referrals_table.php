<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('committee_referrals', function (Blueprint $table) {
            $table->id();

            // CMS agenda item
            $table->unsignedBigInteger('session_document_id');

            // Existing standing committee
            $table->unsignedBigInteger('committee_id');

            $table->text('referral_remarks')->nullable();

            $table->enum('status', [
                'referred',
                'under_review',
                'report_submitted',
                'returned_to_council',
                'closed',
            ])->default('referred');

            $table->unsignedBigInteger('referred_by')->nullable();
            $table->timestamp('referred_at')->nullable();

            $table->text('recommendation')->nullable();
            $table->string('report_file_path')->nullable();
            $table->timestamp('report_submitted_at')->nullable();

            $table->timestamps();

            $table->foreign('session_document_id')
                ->references('id')
                ->on('session_documents')
                ->cascadeOnDelete();

            $table->foreign('committee_id')
                ->references('id')
                ->on('standing_comittee')
                ->restrictOnDelete();

            $table->index(['committee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('committee_referrals');
    }
};