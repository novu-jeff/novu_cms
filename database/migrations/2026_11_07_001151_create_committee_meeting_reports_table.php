<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('committee_meeting_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('committee_meeting_id')
                ->unique()
                ->constrained('committee_meetings')
                ->cascadeOnDelete();

            $table->string('file_path');
            $table->string('original_filename')->nullable();

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('uploaded_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('committee_meeting_reports');
    }
};