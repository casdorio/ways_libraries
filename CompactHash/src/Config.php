<?php

namespace Casdorio\CompactHash;

class Config
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}