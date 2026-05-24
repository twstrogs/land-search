<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('key', 150)->change();
        });

        \App\Models\Setting::updateOrCreate(
            ['key' => 'ai.ollama_url'],
            ['key' => 'ai.ollama_url', 'value' => 'https://ollama.com', 'group' => 'ai', 'type' => 'string', 'description' => 'URL Ollama']
        );

        \App\Models\Setting::updateOrCreate(
            ['key' => 'ai.ollama_model'],
            ['key' => 'ai.ollama_model', 'value' => 'gpt-oss:120b-cloud', 'group' => 'ai', 'type' => 'string', 'description' => 'Model Ollama']
        );

        \App\Models\Setting::updateOrCreate(
            ['key' => 'ai.ollama_api_key'],
            ['key' => 'ai.ollama_api_key', 'value' => '', 'group' => 'ai', 'type' => 'string', 'description' => 'API Key Ollama']
        );
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            //
        });
    }
};
