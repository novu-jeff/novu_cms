<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_proceedings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('session_id')
                ->constrained('session_meetings')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('agenda_item_id');

            $table->string('agenda_type', 30);

            $table->string('entry_type', 30);

            $table->longText('content');

            $table->string('moved_by')->nullable();
            $table->string('seconded_by')->nullable();

            $table->foreignId('recorded_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();

            $table->index(
                ['session_id', 'agenda_type', 'agenda_item_id'],
                'session_proceedings_agenda_idx'
            );

            $table->index(
                ['session_id', 'created_at'],
                'session_proceedings_timeline_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_proceedings');
    }
};
