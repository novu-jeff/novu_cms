<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('committee_meeting_agenda_items', function (Blueprint $table) {
            $table->unique(
                ['committee_meeting_id', 'committee_referral_id'],
                'committee_meeting_referral_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('committee_meeting_agenda_items', function (Blueprint $table) {
            $table->dropUnique('committee_meeting_referral_unique');
        });
    }
};
