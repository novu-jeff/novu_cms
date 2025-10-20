<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barangay_officials', function (Blueprint $table) {
            // Add barangay_id column
            $table->unsignedBigInteger('barangay_id')->nullable()->after('name');

            // Create foreign key to barangays table
            $table->foreign('barangay_id')
                ->references('id')
                ->on('barangays')
                ->onDelete('cascade');

            // Drop old barangay text column if it exists
            if (Schema::hasColumn('barangay_officials', 'barangay')) {
                $table->dropColumn('barangay');
            }
        });
    }

    public function down(): void
    {
        Schema::table('barangay_officials', function (Blueprint $table) {
            // Rollback: add back old column
            $table->string('barangay')->nullable();

            // Drop FK and column
            $table->dropForeign(['barangay_id']);
            $table->dropColumn('barangay_id');
        });
    }
};