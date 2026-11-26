<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('committee_meeting_agenda_actions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('committee_meeting_id');
            $table->unsignedBigInteger('committee_meeting_agenda_item_id');

            $table->foreign('committee_meeting_id', 'cmaa_meeting_fk')
                ->references('id')
                ->on('committee_meetings')
                ->cascadeOnDelete();

            $table->foreign('committee_meeting_agenda_item_id', 'cmaa_agenda_item_fk')
                ->references('id')
                ->on('committee_meeting_agenda_items')
                ->cascadeOnDelete();

            $table->string('action', 30);
            $table->text('reason')->nullable();
            $table->text('recommendation')->nullable();

            $table->unsignedBigInteger('acted_by')->nullable()->index();
            $table->timestamp('acted_at')->nullable();

            $table->timestamps();

            $table->index(
                ['committee_meeting_id', 'acted_at'],
                'committee_meeting_agenda_action_date_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('committee_meeting_agenda_actions');
    }
};