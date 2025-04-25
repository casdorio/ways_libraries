<?php

namespace Casdorio\CompactHash;

class Encoder
{
    protected string $salt;
    protected int $key;
    protected int $length;

    public function __construct()
    {
        $this->salt = Config::get('HASHIDS_SALT', 'casdorio_salt');
        $this->key = (int)Config::get('HASHIDS_KEY', 1);
        $this->length = (int)Config::get('HASHIDS_LENGTH', 10);
    }

    public function encode(int $value): string
    {
        $val = $value * $this->key;
        return substr(base64_encode($val . $this->salt), 0, $this->length);
    }

    public function decode(string $encoded): int
    {
        $decoded = base64_decode($encoded);
        if (!$decoded || !str_contains($decoded, $this->salt)) {
            return 0;
        }

        $original = str_replace($this->salt, '', $decoded);
        return (int)((int)$original / $this->key);
    }
}