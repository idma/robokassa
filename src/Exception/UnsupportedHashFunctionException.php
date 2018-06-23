<?php

namespace Idma\Robokassa\Exception;


class UnsupportedHashFunctionException extends ConfigException
{
    public function __construct($message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct('Hash function is unsupported.', $code, $previous);
    }
}