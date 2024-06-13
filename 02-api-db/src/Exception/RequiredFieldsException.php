<?php

namespace App\Exception;

use Exception;

class RequiredFieldsException extends Exception
{
    public function __construct(
        private array $errors
    ) {

    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
