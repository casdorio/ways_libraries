<?php
declare(strict_types=1);

namespace Casdorio\CompactHash\Exception;

class InvalidHashException extends \InvalidArgumentException
{
    public function __construct(string $message = "Invalid hash provided")
    {
        parent::__construct($message);
    }
}