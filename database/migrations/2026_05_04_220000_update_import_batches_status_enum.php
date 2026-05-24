<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change enum to support more status values
        DB::statement("ALTER TABLE import_batches MODIFY COLUMN status VARCHAR(50) DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE import_batches MODIFY COLUMN status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending'");
    }
};
