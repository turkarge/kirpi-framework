<?php

declare(strict_types=1);

namespace Core\Support;

final class RequestContext
{
    private static ?string $requestId = null;

    public static function setRequestId(string $requestId): void
    {
        self::$requestId = trim($requestId) !== '' ? trim($requestId) : null;
    }

    public static function requestId(): ?string
    {
        return self::$requestId;
    }
}

