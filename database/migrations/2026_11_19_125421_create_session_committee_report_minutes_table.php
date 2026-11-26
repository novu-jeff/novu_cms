<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_committee_report_minutes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('session_id')
                ->constrained('session_meetings')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('committee_report_agenda_item_id');

            $table->foreign(
                'committee_report_agenda_item_id',
                'scrm_agenda_item_fk'
            )->references('id')
                ->on('committee_report_agenda_items')
                ->onDelete('cascade');

            $table->text('discussion')->nullable();
            $table->text('motion')->nullable();
            $table->string('motion_moved_by')->nullable();
            $table->string('motion_seconded_by')->nullable();
            $table->text('official_reference')->nullable();
            $table->text('decision')->nullable();

            $table->foreignId('prepared_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(
                ['session_id', 'committee_report_agenda_item_id'],
                'session_committee_report_minutes_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_committee_report_minutes');
    }
};