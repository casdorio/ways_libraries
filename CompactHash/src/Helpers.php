<?php

use Casdorio\CompactHash\CompactHash;

if (!function_exists('encryptNumber')) {
    function encryptNumber(int $value): int|string
    {
        return (new CompactHash())->encryptNumber($value);
    }
}

if (!function_exists('decryptNumber')) {
    function decryptNumber(string $value): int
    {
        return (new CompactHash())->decryptNumber($value);
    }
}

if (!function_exists('encryptNumberArray')) {
    function encryptNumberArray(array $data, array $indices): array
    {
        return (new CompactHash())->encryptArray($data, $indices);
    }
}

if (!function_exists('decryptNumberArray')) {
    function decryptNumberArray(array $data, array $indices): array
    {
        return (new CompactHash())->decryptArray($data, $indices);
    }
}