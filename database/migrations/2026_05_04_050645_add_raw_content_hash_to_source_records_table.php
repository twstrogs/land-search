<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('source_records', function (Blueprint $table) {
            $table->string('raw_content_hash', 64)->nullable()->after('raw_content');
            $table->index('raw_content_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('source_records', function (Blueprint $table) {
            $table->dropIndex(['raw_content_hash']);
            $table->dropColumn('raw_content_hash');
        });
    }
};
