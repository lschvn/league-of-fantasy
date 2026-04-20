<?php

namespace App\Services;

use RuntimeException;

class ApiException extends RuntimeException
{
    public function __construct(
        public readonly int $status,
        string $message,
        protected array $errors = [],
        protected mixed $responseBody = null,
    ) {
        parent::__construct($message, $status);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function responseBody(): mixed
    {
        return $this->responseBody;
    }
}
