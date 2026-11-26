<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('session_meetings', function (Blueprint $table) {
            if (! Schema::hasColumn('session_meetings', 'minutes_finalized_by')) {
                $table->unsignedBigInteger('minutes_finalized_by')->nullable();
            }

            if (! Schema::hasColumn('session_meetings', 'minutes_finalized_at')) {
                $table->timestamp('minutes_finalized_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('session_meetings', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('session_meetings', 'minutes_finalized_by')) {
                $columns[] = 'minutes_finalized_by';
            }

            if (Schema::hasColumn('session_meetings', 'minutes_finalized_at')) {
                $columns[] = 'minutes_finalized_at';
            }

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};