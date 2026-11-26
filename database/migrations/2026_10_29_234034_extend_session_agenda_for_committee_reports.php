<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Identify which agenda table the current item belongs to.
        Schema::table('session_meetings', function (Blueprint $table) {
            $table->string('current_agenda_item_type', 50)
                ->nullable()
                ->after('current_agenda_item_id');
        });

        // Track the progress of committee reports during a session.
        Schema::table('committee_report_agenda_items', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'in_progress',
                'completed',
                'postponed',
                'referred',
                'tabled',
            ])->default('pending')->after('agenda_order');
        });

        // Allow votes to reference either agenda item type.
        Schema::table('session_votes', function (Blueprint $table) {
            $table->unsignedBigInteger('session_document_id')
                ->nullable()
                ->change();

            $table->unsignedBigInteger('committee_report_agenda_item_id')
                ->nullable()
                ->after('session_document_id');

            $table->index(
                'committee_report_agenda_item_id',
                'session_votes_committee_report_index'
            );
        });

        // Allow agenda actions to reference either agenda item type.
        Schema::table('session_agenda_actions', function (Blueprint $table) {
            $table->unsignedBigInteger('session_document_id')
                ->nullable()
                ->change();

            $table->unsignedBigInteger('committee_report_agenda_item_id')
                ->nullable()
                ->after('session_document_id');

            $table->index(
                'committee_report_agenda_item_id',
                'session_agenda_actions_committee_report_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('session_agenda_actions', function (Blueprint $table) {
            $table->dropIndex('session_agenda_actions_committee_report_index');
            $table->dropColumn('committee_report_agenda_item_id');
        });

        Schema::table('session_votes', function (Blueprint $table) {
            $table->dropIndex('session_votes_committee_report_index');
            $table->dropColumn('committee_report_agenda_item_id');
        });

        Schema::table('committee_report_agenda_items', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('session_meetings', function (Blueprint $table) {
            $table->dropColumn('current_agenda_item_type');
        });
    }
};