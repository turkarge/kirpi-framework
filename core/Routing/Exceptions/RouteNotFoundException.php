<?php

declare(strict_types=1);

namespace Core\Routing\Exceptions;

use Core\Exception\HttpException;

class RouteNotFoundException extends HttpException
{
    public function __construct(string $uri = '')
    {
        parent::__construct(
            404,
            $uri ? "Route [{$uri}] not found." : 'Not Found'
        );
    }
}