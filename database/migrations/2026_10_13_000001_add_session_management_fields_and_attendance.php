<?php

use App\Models\SessionMeeting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Resolve the same database connection used by SessionMeeting.
     * Run this migration in the LIS application that owns the CMS session tables.
     */
    private function schema()
    {
        $connection = (new SessionMeeting())->getConnectionName();

        return Schema::connection($connection ?: config('database.default'));
    }

    public function up(): void
    {
        $schema = $this->schema();

        $schema->table('session_meetings', function (Blueprint $table) use ($schema) {
            if (!$schema->hasColumn('session_meetings', 'session_type')) {
                $table->string('session_type', 30)->default('regular');
            }
            if (!$schema->hasColumn('session_meetings', 'start_time')) {
                $table->time('start_time')->nullable();
            }
            if (!$schema->hasColumn('session_meetings', 'end_time')) {
                $table->time('end_time')->nullable();
            }
            if (!$schema->hasColumn('session_meetings', 'venue')) {
                $table->string('venue', 255)->nullable();
            }
            if (!$schema->hasColumn('session_meetings', 'session_number')) {
                $table->unsignedInteger('session_number')->nullable();
            }
            if (!$schema->hasColumn('session_meetings', 'legislative_term')) {
                $table->string('legislative_term', 50)->nullable();
            }
            if (!$schema->hasColumn('session_meetings', 'presiding_officer')) {
                $table->string('presiding_officer', 150)->nullable();
            }
            if (!$schema->hasColumn('session_meetings', 'quorum_required')) {
                $table->unsignedSmallInteger('quorum_required')->nullable();
            }
        });

        if (!$schema->hasTable('session_attendances')) {
            $schema->create('session_attendances', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('session_id')->index();
                // Name/role are captured as a snapshot to avoid assuming the
                // primary key/table structure of the existing council-member model.
                $table->string('attendee_name', 150);
                $table->string('attendee_role', 100)->nullable();
                $table->string('attendance_status', 20)->default('present');
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->index(['session_id', 'attendance_status'], 'session_attendance_status_idx');
            });
        }
    }

    public function down(): void
    {
        $schema = $this->schema();

        $schema->dropIfExists('session_attendances');

        if ($schema->hasTable('session_meetings')) {
            $schema->table('session_meetings', function (Blueprint $table) use ($schema) {
                foreach ([
                    'session_type',
                    'start_time',
                    'end_time',
                    'venue',
                    'session_number',
                    'legislative_term',
                    'presiding_officer',
                    'quorum_required',
                ] as $column) {
                    if ($schema->hasColumn('session_meetings', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
