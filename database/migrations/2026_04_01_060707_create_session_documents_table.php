<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_documents', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('session_id');

            $table->unsignedBigInteger('document_id');

            $table->integer('agenda_order')->default(1);

            $table->timestamps();


            $table->foreign('session_id')
                ->references('id')
                ->on('session_meetings')
                ->onDelete('cascade');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_documents');
    }
};