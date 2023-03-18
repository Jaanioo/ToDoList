<?php

namespace App\Exception;

class ValidationException extends \Exception
{
    private $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
        $errorMessages = [];
        foreach ($errors as $error => $message) {
            $errorMessages[$error] = $message;
        }
        parent::__construct(json_encode($errorMessages));
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
