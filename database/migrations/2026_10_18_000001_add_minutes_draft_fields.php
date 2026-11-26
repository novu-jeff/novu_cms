<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('session_meetings', function (Blueprint $table) {
            $table->string('quorum_confirmation', 30)->nullable();
            $table->string('previous_minutes_status', 40)->nullable();
        });

        Schema::table('session_minutes', function (Blueprint $table) {
            $table->string('motion_moved_by')->nullable();
            $table->string('motion_seconded_by')->nullable();
            $table->string('official_reference')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('session_minutes', function (Blueprint $table) {
            $table->dropColumn([
                'motion_moved_by',
                'motion_seconded_by',
                'official_reference',
            ]);
        });

        Schema::table('session_meetings', function (Blueprint $table) {
            $table->dropColumn([
                'quorum_confirmation',
                'previous_minutes_status',
            ]);
        });
    }
};
