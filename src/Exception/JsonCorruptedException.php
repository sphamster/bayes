<?php

declare(strict_types=1);

namespace Sphamster\Exception;

use JsonException;
use Throwable;

class JsonCorruptedException extends JsonException
{
    public function __construct(
        string     $message = 'JSON data is corrupted.',
        int        $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
