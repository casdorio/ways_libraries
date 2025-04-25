<?php
declare(strict_types=1);

namespace Casdorio\CompactHash;

interface CompactHashInterface
{
    /**
     * @param array<int|string> $numbers Array de inteiros ou strings numéricas
     * @return string Hash gerado
     * @throws InvalidNumberException
     */
    public function encode(array $numbers): string;

    /**
     * @return array<string> Sempre retorna strings para uniformidade
     * @throws InvalidHashException
     */
    public function decode(string $hash): array;
}