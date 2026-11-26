
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_votes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('session_document_id');

            $table->string('motion_title', 255);
            $table->text('motion_description')->nullable();

            $table->enum('voting_method', [
                'roll_call',
                'secret',
            ])->default('roll_call');

            $table->enum('status', [
                'open',
                'closed',
                'finalized',
            ])->default('open');

            $table->unsignedBigInteger('opened_by')->nullable();
            $table->timestamp('opened_at')->nullable();

            $table->unsignedBigInteger('closed_by')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->string('result', 50)->nullable();
            $table->unsignedBigInteger('finalized_by')->nullable();
            $table->timestamp('finalized_at')->nullable();

            $table->timestamps();

            $table->index(
                ['session_id', 'session_document_id'],
                'session_votes_agenda_index'
            );

            $table->index(
                ['session_id', 'status'],
                'session_votes_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_votes');
    }
};
