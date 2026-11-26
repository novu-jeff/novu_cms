<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('session_attendances', function (Blueprint $table) {
            $table->unsignedBigInteger('member_account_id')
                ->nullable()
                ->after('session_id')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('session_attendances', function (Blueprint $table) {
            $table->dropIndex(['member_account_id']);
            $table->dropColumn('member_account_id');
        });
    }
};
