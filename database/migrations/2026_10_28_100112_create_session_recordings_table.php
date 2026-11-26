<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_recordings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('session_id')
                ->constrained('session_meetings')
                ->cascadeOnDelete();

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('original_filename');
            $table->string('disk')->default('local');
            $table->string('file_path');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');

            $table->string('status', 30)->default('uploaded')->index();
            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->index(['session_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_recordings');
    }
};