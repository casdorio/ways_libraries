<?php

/**
 * Copyright (c) Carlos Oliveira.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://github.com/casdorio/hashids
 */

namespace Casdorio\Hashids;

interface HashidsInterface
{
    /**
     * Encode parameters to generate a hash.
     *
     * @param array<int, int|string>|int|string ...$numbers
     */
    public function encode(...$numbers): string;

    /**
     * Decode a hash to the original parameter values.
     *
     * @return array<int, int>
     */
    public function decode(string $hash): array;

    /**
     * Encode hexadecimal values and generate a hash string.
     */
    public function encodeHex(string $str): string;

    /**
     * Decode a hexadecimal hash.
     */
    public function decodeHex(string $hash): string;
}
