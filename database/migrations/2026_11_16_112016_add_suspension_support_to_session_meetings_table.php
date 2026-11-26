<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add suspended to the existing session status values.
        DB::statement("
            ALTER TABLE session_meetings
            MODIFY status ENUM(
                'upcoming',
                'ongoing',
                'suspended',
                'completed'
            ) NOT NULL DEFAULT 'upcoming'
        ");

        // Keep a separate record for every suspension cycle.
        Schema::create('session_suspensions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('session_id')->index();

            $table->text('reason');

            $table->unsignedBigInteger('suspended_by')->nullable();
            $table->unsignedBigInteger('resumed_by')->nullable();

            $table->dateTime('suspended_at');
            $table->dateTime('resumed_at')->nullable();

            $table->timestamps();

            $table->index(
                ['session_id', 'resumed_at'],
                'session_suspensions_active_idx'
            );
        });
    }

    public function down(): void
    {
        // Restore suspended sessions to ongoing before removing the status.
        DB::table('session_meetings')
            ->where('status', 'suspended')
            ->update(['status' => 'ongoing']);

        Schema::dropIfExists('session_suspensions');

        DB::statement("
            ALTER TABLE session_meetings
            MODIFY status ENUM(
                'upcoming',
                'ongoing',
                'completed'
            ) NOT NULL DEFAULT 'upcoming'
        ");
    }
};
