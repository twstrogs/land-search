<?php

namespace App\AI\Pipeline;

/**
 * Interface for pipeline stage implementations
 */
interface PipelineStageInterface
{
    /**
     * Execute the stage with given data
     * 
     * @param array $data Pipeline data from previous stages
     * @return array Modified pipeline data
     */
    public function execute(array $data): array;

    /**
     * Check if this stage is critical for pipeline success
     * If true, failure will halt the entire pipeline
     */
    public function isCritical(): bool;

    /**
     * Get the stage name
     */
    public function getName(): string;

    /**
     * Get stage description
     */
    public function getDescription(): string;

    /**
     * Get required input data keys
     */
    public function getRequiredInputs(): array;

    /**
     * Get output data keys that this stage produces
     */
    public function getOutputs(): array;

    /**
     * Validate input data before execution
     * 
     * @throws \App\AI\Exceptions\StageValidationException
     */
    public function validateInput(array $data): void;
}
