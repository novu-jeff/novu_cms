<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('committee_meeting_attendances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('committee_meeting_id')
                ->constrained('committee_meetings')
                ->cascadeOnDelete();

            $table->foreignId('committee_member_id')
                ->nullable()
                ->constrained('standing_comittee_member')
                ->nullOnDelete();

            // Historical snapshots
            $table->string('attendee_name', 150);
            $table->string('attendee_role', 100)->nullable();

            $table->string('attendance_status', 20)->default('present');
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->unique(
                ['committee_meeting_id', 'committee_member_id'],
                'committee_meeting_member_unique'
            );

            $table->index(
                ['committee_meeting_id', 'attendance_status'],
                'committee_meeting_attendance_status_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('committee_meeting_attendances');
    }
};