<?php
declare(strict_types=1);

namespace Casdorio\CompactHash\Exception;

class InvalidNumberException extends \InvalidArgumentException
{
    public function __construct(string $message = "Invalid number provided")
    {
        parent::__construct($message);
    }
}