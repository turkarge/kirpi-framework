<?php

declare(strict_types=1);

namespace Core\Http\Client;

class RequestException extends \RuntimeException
{
    public function __construct(
        private readonly Response $response,
    ) {
        parent::__construct(
            "HTTP request failed with status [{$response->status()}].",
            $response->status()
        );
    }

    public function response(): Response
    {
        return $this->response;
    }

    public function status(): int
    {
        return $this->response->status();
    }
}