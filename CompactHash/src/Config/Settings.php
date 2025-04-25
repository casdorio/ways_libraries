<?php
declare(strict_types=1);

namespace Casdorio\CompactHash\Config;

final readonly class Settings
{
    public function __construct(
        public string $salt = '',
        public int $minHashLength = 0,
        public string $alphabet = Alphabet::DEFAULT
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->minHashLength < 0) {
            throw new \InvalidArgumentException('Minimum hash length cannot be negative');
        }
    }
}