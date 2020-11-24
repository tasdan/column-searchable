<?php

namespace TasDan\ColumnSearchable\Exceptions;

use Exception;

class ColumnSearchableFieldException extends Exception
{

    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        switch ($code) {
            case 0:
                $message = 'Invalid field arguments.';
                break;
        }

        parent::__construct($message, $code, $previous);
    }
}
