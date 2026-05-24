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
        Schema::table('import_batches', function (Blueprint $table) {
            $table->unsignedInteger('skipped_records')->default(0)->after('failed_records');
            $table->unsignedInteger('duplicate_records')->default(0)->after('skipped_records');
            $table->timestamp('started_at')->nullable()->after('ai_provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('import_batches', function (Blueprint $table) {
            $table->dropColumn(['skipped_records', 'duplicate_records', 'started_at']);
        });
    }
};
