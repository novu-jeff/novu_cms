
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('session_meetings', function (Blueprint $table) {
            $table->string('minutes_status', 30)
                ->default('draft')->index();

            $table->foreignId('minutes_submitted_by')
                ->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('minutes_submitted_at')->nullable();

            $table->foreignId('minutes_reviewed_by')
                ->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('minutes_reviewed_at')->nullable();
        });

        Schema::create('session_minute_reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('session_id')
                ->constrained('session_meetings')->cascadeOnDelete();

            $table->foreignId('reviewer_id')
                ->constrained('users')->cascadeOnDelete();

            $table->string('action', 40);
            $table->text('comments')->nullable();

            $table->timestamps();

            $table->index(['session_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_minute_reviews');

        Schema::table('session_meetings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('minutes_submitted_by');
            $table->dropConstrainedForeignId('minutes_reviewed_by');

            $table->dropColumn([
                'minutes_status',
                'minutes_submitted_at',
                'minutes_reviewed_at',
            ]);
        });
    }
};
