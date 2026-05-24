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
        Schema::create('prompt_versions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->index(); // task type
            $table->string('version', 50); // e.g., v1.0, v1.1
            $table->text('system_prompt');
            $table->json('few_shot_examples')->nullable();
            $table->json('output_schema')->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Unique constraint
            $table->unique(['name', 'version']);
        });

        Schema::create('evaluation_datasets', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->unique();
            $table->text('description')->nullable();
            $table->json('ground_truth');
            $table->json('test_samples');
            $table->integer('total_samples');
            $table->string('source', 100)->nullable(); // Where the data came from
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('evaluation_results', function (Blueprint $table) {
            $table->id();
            $table->string('test_dataset_name', 255);
            $table->enum('method', ['regex', 'nlp', 'llm', 'hybrid']);
            $table->string('provider', 50)->nullable(); // For LLM methods
            $table->string('model', 100)->nullable();
            
            // Overall metrics
            $table->decimal('accuracy', 5, 4);
            $table->decimal('precision_score', 5, 4);
            $table->decimal('recall_score', 5, 4);
            $table->decimal('f1_score', 5, 4);
            
            // Per-field metrics
            $table->json('field_metrics')->nullable();
            
            // Statistics
            $table->integer('total_samples');
            $table->integer('processing_time_ms_avg');
            $table->decimal('cost_per_1k_tokens', 10, 6)->nullable();
            $table->integer('error_count')->default(0);
            
            // Additional info
            $table->json('config')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();

            // Indexes
            $table->index(['method', 'created_at']);
            $table->index(['test_dataset_name', 'created_at']);
            $table->index(['provider', 'created_at']);
        });

        Schema::create('post_embeddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('embedding');
            $table->string('embedding_model', 100)->nullable();
            $table->timestamp('indexed_at')->nullable();
            $table->timestamps();

            $table->index('post_id');
        });

        Schema::create('extraction_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('prompt_version_id')->nullable()->constrained('prompt_versions')->nullOnDelete();
            $table->text('raw_content');
            $table->json('raw_response')->nullable();
            $table->json('normalized_data')->nullable();
            $table->decimal('confidence_score', 5, 4)->nullable();
            $table->integer('processing_time_ms')->default(0);
            $table->enum('validation_status', ['pending', 'passed', 'failed', 'warning'])->default('pending');
            $table->json('validation_errors')->nullable();
            $table->string('ai_provider', 50)->nullable();
            $table->string('ai_model', 100)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['post_id', 'created_at']);
            $table->index(['validation_status', 'created_at']);
            $table->index(['confidence_score']);
        });

        Schema::create('prompt_experiments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->foreignId('prompt_a_id')->nullable()->constrained('prompt_versions')->nullOnDelete();
            $table->foreignId('prompt_b_id')->nullable()->constrained('prompt_versions')->nullOnDelete();
            $table->decimal('split_ratio', 3, 2)->default(0.5);
            $table->enum('status', ['setup', 'running', 'completed', 'paused', 'cancelled'])->default('setup');
            $table->json('config')->nullable();
            $table->json('results')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prompt_experiments');
        Schema::dropIfExists('extraction_logs');
        Schema::dropIfExists('post_embeddings');
        Schema::dropIfExists('evaluation_results');
        Schema::dropIfExists('evaluation_datasets');
        Schema::dropIfExists('prompt_versions');
    }
};
