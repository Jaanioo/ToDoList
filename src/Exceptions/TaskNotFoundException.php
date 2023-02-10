<?php

namespace App\Exceptions;

class TaskNotFoundException extends \Exception
{
    public function __construct(int $id, $code = 0, \Exception $previous = null)
    {
        $message = sprintf('Task with id: "%s" could not be found.', $id);

        parent::__construct($message, $code, $previous);
    }
}