<?php

namespace App\Exceptions;

use RuntimeException;

class ApiException extends RuntimeException
{
    public function __construct(string $message = 'Error al comunicarse con la API de Eventify', int $code = 503)
    {
        parent::__construct($message, $code);
    }
}
