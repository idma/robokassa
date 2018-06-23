<?php

namespace Idma\Robokassa\Exception;


class ConfigException extends \Exception
{
    public function __construct($message = '', $code = 0, \Exception $previous = null)
    {
        $message = $message ? $message : 'Configuration exception.';

        parent::__construct($message, $code, $previous);
    }
}
