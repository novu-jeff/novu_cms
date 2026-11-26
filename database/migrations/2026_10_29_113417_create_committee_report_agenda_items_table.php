<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('committee_report_agenda_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('committee_referral_id')
                ->constrained('committee_referrals')
                ->restrictOnDelete();

            $table->foreignId('session_id')
                ->constrained('session_meetings')
                ->cascadeOnDelete();

            $table->unsignedInteger('agenda_order');
            $table->timestamps();

            $table->unique(
                ['committee_referral_id', 'session_id'],
                'committee_report_agenda_referral_session_unique'
            );

            $table->index(['session_id', 'agenda_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('committee_report_agenda_items');
    }
};
