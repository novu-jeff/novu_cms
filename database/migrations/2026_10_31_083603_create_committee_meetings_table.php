<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('committee_meetings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('committee_id')
                ->constrained('standing_comittee')
                ->restrictOnDelete();

            $table->string('title', 255);
            $table->date('meeting_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('venue', 255)->nullable();

            $table->string('status', 30)->default('scheduled');

            $table->string('presiding_officer', 150)->nullable();
            $table->unsignedSmallInteger('quorum_required')->nullable();

            // Live meeting control
            $table->unsignedBigInteger('current_agenda_item_id')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();

            $table->timestamps();

            $table->index(['committee_id', 'meeting_date']);
            $table->index(['committee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('committee_meetings');
    }
};