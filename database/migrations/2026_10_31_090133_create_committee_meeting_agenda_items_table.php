<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('committee_meeting_agenda_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('committee_meeting_id')
                ->constrained('committee_meetings')
                ->cascadeOnDelete();

            $table->foreignId('committee_referral_id')
                ->constrained('committee_referrals')
                ->restrictOnDelete();

            $table->unsignedInteger('agenda_order');

            $table->string('status', 30)->default('pending');

            $table->timestamps();

            $table->unique(
                ['committee_meeting_id', 'agenda_order'],
                'committee_meeting_agenda_order_unique'
            );

            $table->index(
                ['committee_meeting_id', 'status'],
                'committee_meeting_agenda_status_idx'
            );

            $table->index('committee_referral_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('committee_meeting_agenda_items');
    }
};