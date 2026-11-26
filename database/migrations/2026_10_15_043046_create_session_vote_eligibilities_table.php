
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_vote_eligibilities', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('session_vote_id');
            $table->unsignedBigInteger('member_account_id');

            $table->boolean('eligible')->default(true);
            $table->string('eligibility_reason', 255)->nullable();

            $table->timestamps();

            $table->unique(
                ['session_vote_id', 'member_account_id'],
                'session_vote_eligibility_unique'
            );

            $table->foreign('session_vote_id')
                ->references('id')
                ->on('session_votes')
                ->cascadeOnDelete();

            $table->index('member_account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_vote_eligibilities');
    }
};
