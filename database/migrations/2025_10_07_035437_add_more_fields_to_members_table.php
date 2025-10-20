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
        Schema::table('members', function (Blueprint $table) {
            $table->string('email')->nullable();
            $table->string('contact_number')->nullable();
            $table->text('address')->nullable();
            $table->date('term_start')->nullable();
            $table->date('term_end')->nullable();
            $table->text('achievements')->nullable();
            $table->text('priority_projects')->nullable();
            $table->string('social_facebook')->nullable();
            $table->string('social_twitter')->nullable();
            $table->string('social_instagram')->nullable();
            $table->integer('sort_order')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn([
                'email',
                'contact_number',
                'address',
                'term_start',
                'term_end',
                'achievements',
                'priority_projects',
                'social_facebook',
                'social_twitter',
                'social_instagram',
                'sort_order',
            ]);
        });
    }
};
