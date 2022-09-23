<?php

namespace App\GetVideo;

use Exception;

class ExtractionFailedException extends Exception
{
    public function __construct(private int $errorCode = -1, private string $errorMessage = '')
    {
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}
