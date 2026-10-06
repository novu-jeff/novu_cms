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
        Schema::table('session_meetings', function (Blueprint $table) {

            $table->string('adjournment_time')->nullable();
            $table->string('adjournment_reason')->nullable();
            $table->string('adjournment_by')->nullable();
            $table->string('adjournment_to')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('session_meetings', function (Blueprint $table) {
            //
        });
    }
};
