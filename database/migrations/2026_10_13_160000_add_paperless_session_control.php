<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('session_meetings')) {
            Schema::table('session_meetings', function (Blueprint $table) {
                if (!Schema::hasColumn('session_meetings', 'current_agenda_item_id')) $table->unsignedBigInteger('current_agenda_item_id')->nullable()->index();
                if (!Schema::hasColumn('session_meetings', 'started_at')) $table->timestamp('started_at')->nullable();
                if (!Schema::hasColumn('session_meetings', 'ended_at')) $table->timestamp('ended_at')->nullable();
            });
        }
        if (Schema::hasTable('session_documents') && !Schema::hasColumn('session_documents', 'status')) {
            Schema::table('session_documents', function (Blueprint $table) { $table->string('status', 20)->default('pending')->index(); });
        }
        if (!Schema::hasTable('session_agenda_actions')) {
            Schema::create('session_agenda_actions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('session_id')->index();
                $table->unsignedBigInteger('session_document_id')->index();
                $table->string('action', 20);
                $table->text('reason')->nullable();
                $table->string('referred_to', 255)->nullable();
                $table->unsignedBigInteger('acted_by')->nullable()->index();
                $table->timestamp('acted_at');
                $table->timestamps();
                $table->index(['session_id', 'acted_at']);
            });
        }
    }
    public function down(): void {
        Schema::dropIfExists('session_agenda_actions');
        if (Schema::hasTable('session_documents') && Schema::hasColumn('session_documents', 'status')) {
            Schema::table('session_documents', function (Blueprint $table) { $table->dropColumn('status'); });
        }
        if (Schema::hasTable('session_meetings')) {
            foreach (['current_agenda_item_id', 'started_at', 'ended_at'] as $column) {
                if (Schema::hasColumn('session_meetings', $column)) Schema::table('session_meetings', function (Blueprint $table) use ($column) { $table->dropColumn($column); });
            }
        }
    }
};
