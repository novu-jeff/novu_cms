<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_signing_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('committee_id')
                ->nullable()
                ->after('document_id')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('document_signing_requests', function (Blueprint $table) {
            $table->dropIndex(['committee_id']);
            $table->dropColumn('committee_id');
        });
    }
};