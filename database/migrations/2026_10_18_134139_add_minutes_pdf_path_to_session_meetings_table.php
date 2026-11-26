<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('session_meetings', function (Blueprint $table) {
            $table->string('minutes_pdf_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('session_meetings', function (Blueprint $table) {
            $table->dropColumn('minutes_pdf_path');
        });
    }
};