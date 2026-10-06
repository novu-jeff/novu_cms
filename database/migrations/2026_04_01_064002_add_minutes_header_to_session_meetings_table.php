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

            $table->string('call_to_order')->nullable();
            $table->string('prayer')->nullable();
            $table->string('roll_call')->nullable();
        
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
