<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_extractions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_record_id')->nullable()->constrained()->nullOnDelete();
            $table->json('raw_response')->comment('JSON thô từ AI');
            $table->json('normalized_data')->nullable()->comment('Dữ liệu sau chuẩn hóa');
            $table->decimal('confidence', 5, 4)->nullable();
            $table->integer('tokens_used')->nullable();
            $table->integer('processing_time_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_extractions');
    }
};
