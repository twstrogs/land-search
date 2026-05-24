<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL enum requires rebuilding the column to add new values
        // Using raw SQL for MySQL compatibility
        DB::statement("ALTER TABLE source_records MODIFY COLUMN status ENUM('pending', 'processing', 'completed', 'failed', 'duplicate', 'skipped') DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE source_records MODIFY COLUMN status ENUM('pending', 'processing', 'completed', 'failed', 'duplicate') DEFAULT 'pending'");
    }
};
