<?php

/**
 * This file is part of Robokassa package.
 *
 * (c) 2014 IDM Agency (http://idma.ru)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Idma\Robokassa\Exception;

/**
 * Class EmptyDescriptionException
 *
 * @author JhaoDa <jhaoda@gmail.com>
 *
 * @package Idma\Robokassa\Exception
 */
class ReceiptDataException extends PaymentException
{
    public function __construct($message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct('This receipt data: sum, name, quantity, tax are required and cannot be empty.', $code, $previous);
    }
}
