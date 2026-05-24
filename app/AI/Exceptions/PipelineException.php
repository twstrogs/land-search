<?php

namespace App\AI\Exceptions;

/**
 * Exception for pipeline-level errors
 */
class PipelineException extends \Exception
{
    protected string $pipelineId;
    protected array $failedStages;
    protected bool $partialSuccess;

    public function __construct(
        string $message,
        string $pipelineId = '',
        array $failedStages = [],
        bool $partialSuccess = false,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->pipelineId = $pipelineId;
        $this->failedStages = $failedStages;
        $this->partialSuccess = $partialSuccess;
    }

    public function getPipelineId(): string
    {
        return $this->pipelineId;
    }

    public function getFailedStages(): array
    {
        return $this->failedStages;
    }

    public function isPartialSuccess(): bool
    {
        return $this->partialSuccess;
    }

    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'pipeline_id' => $this->pipelineId,
            'failed_stages' => $this->failedStages,
            'partial_success' => $this->partialSuccess,
        ];
    }
}
