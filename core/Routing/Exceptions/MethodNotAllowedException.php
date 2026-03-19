<?php

declare(strict_types=1);

namespace Core\Routing\Exceptions;

use Core\Exception\HttpException;

class MethodNotAllowedException extends HttpException
{
    public function __construct(private readonly array $allowedMethods = [])
    {
        parent::__construct(405, 'Method Not Allowed');
    }

    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }
}