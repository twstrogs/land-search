<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            
            // Content fields
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->longText('raw_content')->nullable();
            
            // Price
            $table->string('price_text')->nullable();
            $table->decimal('price_value', 18, 2)->nullable()->comment('Giá đã chuẩn hóa sang VND');
            
            // Area
            $table->string('area_text')->nullable();
            $table->decimal('area_value', 12, 2)->nullable()->comment('Diện tích m2');
            
            // Frontage
            $table->json('frontage_texts')->nullable();
            $table->integer('frontage_count')->default(0);
            $table->string('depth_text')->nullable();
            
            // Location
            $table->string('address_text')->nullable();
            $table->string('ward')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            
            // Property info
            $table->string('property_type')->nullable();
            
            // Media & Links
            $table->string('facebook_url')->nullable();
            $table->string('image_url')->nullable();
            
            // Author info (from scraper)
            $table->string('author_id')->nullable();
            $table->string('author_name')->nullable();
            
            // Meta
            $table->decimal('confidence', 5, 4)->nullable();
            $table->dateTime('published_at')->nullable();
            $table->string('source_group')->nullable();
            $table->string('hash')->nullable()->unique();
            
            $table->timestamps();
            
            // Indexes for search
            $table->index('price_value');
            $table->index('area_value');
            $table->index('property_type');
            $table->index('district');
            $table->index('city');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
