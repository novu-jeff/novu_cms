<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('standing_comittee_member', function (Blueprint $table) {
            $table->unsignedBigInteger('member_id')
                ->nullable()
                ->after('standing_comittee_id');

            $table->date('term_start')
                ->nullable()
                ->after('position');

            $table->date('term_end')
                ->nullable()
                ->after('term_start');

            $table->foreign('member_id')
                ->references('id')
                ->on('members')
                ->nullOnDelete();

            $table->index('member_id');
        });
    }

    public function down(): void
    {
        Schema::table('standing_comittee_member', function (Blueprint $table) {
            $table->dropForeign(['member_id']);
            $table->dropIndex(['member_id']);

            $table->dropColumn([
                'member_id',
                'term_start',
                'term_end',
            ]);
        });
    }
};