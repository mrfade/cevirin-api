<?php

namespace App\GetVideo\Exceptions;

use Exception;

class ExtractionFailedException extends Exception
{
    public function __construct(private string $errorCode, private string $errorMessage = '')
    {
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}
