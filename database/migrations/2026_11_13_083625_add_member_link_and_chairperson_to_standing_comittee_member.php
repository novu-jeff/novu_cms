
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('standing_comittee_member', function (Blueprint $table) {
            $table->boolean('is_chairperson')
                ->default(false)
                ->after('position');

            $table->index(
                ['standing_comittee_id', 'member_id'],
                'committee_member_lookup_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('standing_comittee_member', function (Blueprint $table) {
            $table->dropIndex('committee_member_lookup_idx');
            $table->dropColumn('is_chairperson');
        });
    }
};
