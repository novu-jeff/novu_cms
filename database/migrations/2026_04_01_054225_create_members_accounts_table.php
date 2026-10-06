<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_accounts', function (Blueprint $table) {
            $table->id();

            // Link to members profile (1:1)
            $table->foreignId('member_id')
                  ->constrained('members')
                  ->cascadeOnDelete()
                  ->unique();

            // Credentials
            $table->string('email')->unique();
            $table->string('password');

            // Account control
            $table->boolean('is_active')->default(true)->index();

            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_accounts');
    }
};