<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facebook_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->boolean('enabled')->default(true);
            $table->integer('scrape_limit')->default(20);
            $table->integer('priority')->default(0);
            $table->string('status')->default('idle');
            $table->text('last_error')->nullable();
            $table->timestamp('last_scrape_at')->nullable();
            $table->integer('posts_scraped')->default(0);
            $table->timestamps();

            $table->index('enabled');
            $table->index('priority');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facebook_groups');
    }
};
