
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('committee_meeting_report_referrals', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('committee_meeting_report_id');
            $table->unsignedBigInteger('committee_referral_id');

            $table->timestamps();

            $table->foreign(
                'committee_meeting_report_id',
                'cmrr_report_fk'
            )->references('id')
                ->on('committee_meeting_reports')
                ->cascadeOnDelete();

            $table->foreign(
                'committee_referral_id',
                'cmrr_referral_fk'
            )->references('id')
                ->on('committee_referrals')
                ->cascadeOnDelete();

            $table->unique(
                ['committee_meeting_report_id', 'committee_referral_id'],
                'cmrr_report_referral_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('committee_meeting_report_referrals');
    }
};
