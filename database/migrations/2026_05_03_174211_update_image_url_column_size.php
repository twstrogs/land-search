<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->text('image_url')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('posts')
            ->whereNotNull('image_url')
            ->whereRaw('CHAR_LENGTH(image_url) > 255')
            ->update([
                'image_url' => DB::raw('LEFT(image_url, 255)'),
            ]);

        Schema::table('posts', function (Blueprint $table) {
            $table->string('image_url', 255)->nullable()->change();
        });
    }
};
