<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_previous_minutes_agenda_items', function (Blueprint $table) {
            $table->id();

            // Current session where the previous minutes are presented
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('previous_session_id');

            $table->foreign('session_id', 'spma_session_fk')
                ->references('id')
                ->on('session_meetings')
                ->cascadeOnDelete();

            $table->foreign('previous_session_id', 'spma_previous_session_fk')
                ->references('id')
                ->on('session_meetings')
                ->restrictOnDelete();

            $table->unsignedInteger('agenda_order');

            $table->string('status')->default('pending');
            // pending, in_progress, completed

            $table->string('decision')->nullable();
            // approved, approved_with_corrections, deferred

            $table->text('motion_moved_by')->nullable();
            $table->text('motion_seconded_by')->nullable();
            $table->text('remarks')->nullable();

            $table->unsignedBigInteger('decision_recorded_by')->nullable();

            $table->foreign('decision_recorded_by', 'spma_decision_user_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->timestamp('decision_recorded_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['session_id', 'previous_session_id'],
                'session_previous_minutes_unique'
            );

            $table->index(
                ['session_id', 'agenda_order'],
                'session_previous_minutes_agenda_order_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_previous_minutes_agenda_items');
    }
};