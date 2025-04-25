<?php

namespace Casdorio\CompactHash;

class CompactHash
{
    protected Encoder $encoder;
    protected bool $enabled;

    public function __construct()
    {
        $this->encoder = new Encoder();
        $this->enabled = Config::get('ENCRYPTION', 'true') !== 'false';
    }

    public function encryptNumber(int $value): int|string
    {
        if (!$this->enabled || $value === 0) {
            return $value;
        }

        return $this->encoder->encode($value);
    }

    public function decryptNumber(string $value): int
    {
        if (!$this->enabled || $value === 'null' || $value === 'undefined') {
            return (int)$value;
        }

        return $this->encoder->decode($value);
    }

    public function encryptArray(array $data, array $keys): array
    {
        array_walk_recursive($data, function (&$value, $key) use ($keys) {
            if (in_array($key, $keys, true)) {
                $value = $this->encryptNumber((int)$value);
            }
        });

        return $data;
    }

    public function decryptArray(array $data, array $keys): array
    {
        array_walk_recursive($data, function (&$value, $key) use ($keys) {
            if (in_array($key, $keys, true)) {
                $value = $this->decryptNumber((string)$value);
            }
        });

        return $data;
    }
}