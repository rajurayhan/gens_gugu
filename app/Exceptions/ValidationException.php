<?php

namespace App\Exceptions;

use Exception;

class ValidationException extends Exception
{
    private $failures;
    private $stop_flag;

    public function __construct($failures, $stop_flag = null)
    {
        $this->failures = $failures;
        $this->stop_flag = $stop_flag;
    }

    public function failures()
    {
        return $this->failures;
    }

    public function stopFlag()
    {
        return $this->stop_flag;
    }
}
