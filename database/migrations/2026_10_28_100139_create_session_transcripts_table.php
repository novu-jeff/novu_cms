<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_transcripts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('recording_id')
                ->unique()
                ->constrained('session_recordings')
                ->cascadeOnDelete();

            $table->longText('transcript_text')->nullable();

            $table->string('language', 20)->nullable();
            $table->string('provider', 100)->nullable();

            $table->json('provider_metadata')->nullable();

            $table->string('status', 30)
                ->default('pending')
                ->index();

            $table->text('error_message')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_transcripts');
    }
};