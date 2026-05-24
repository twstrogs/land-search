<?php

namespace App\AI\Exceptions;

/**
 * Exception for pipeline stage errors
 */
class StageException extends \Exception
{
    public const VALIDATION_ERROR = 'validation_error';
    public const EXTRACTION_ERROR = 'extraction_error';
    public const NORMALIZATION_ERROR = 'normalization_error';
    public const STORAGE_ERROR = 'storage_error';
    public const TIMEOUT_ERROR = 'timeout_error';

    protected string $stageName;
    protected string $errorType;
    protected array $context;

    public function __construct(
        string $stageName,
        string $message,
        string $errorType = 'unknown',
        array $context = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->stageName = $stageName;
        $this->errorType = $errorType;
        $this->context = $context;
    }

    public function getStageName(): string
    {
        return $this->stageName;
    }

    public function getErrorType(): string
    {
        return $this->errorType;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function toArray(): array
    {
        return [
            'stage' => $this->stageName,
            'type' => $this->errorType,
            'message' => $this->getMessage(),
            'context' => $this->context,
        ];
    }

    public static function validation(string $stage, string $message, array $context = []): self
    {
        return new self($stage, $message, self::VALIDATION_ERROR, $context);
    }

    public static function extraction(string $stage, string $message, array $context = []): self
    {
        return new self($stage, $message, self::EXTRACTION_ERROR, $context);
    }

    public static function timeout(string $stage, string $message, array $context = []): self
    {
        return new self($stage, $message, self::TIMEOUT_ERROR, $context);
    }
}
