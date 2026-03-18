<?php

declare(strict_types=1);

namespace Core\Exception;

class ValidationException extends \RuntimeException
{
    public function __construct(
        private readonly array $errors,
        string $message = 'The given data was invalid.',
    ) {
        parent::__construct($message, 422);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}