<?php

/**
 * Copyright (c) Carlos Oliveira.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://github.com/casdorio/hashids
 */

namespace Casdorio\Hashids\Math;

class BCMath implements MathInterface
{
    public function add($a, $b)
    {
        return bcadd($a, $b, 0);
    }

    public function multiply($a, $b)
    {
        return bcmul($a, $b, 0);
    }

    public function divide($a, $b)
    {
        return bcdiv($a, $b, 0);
    }

    public function mod($n, $d)
    {
        return bcmod($n, $d);
    }

    public function greaterThan($a, $b)
    {
        return bccomp($a, $b, 0) > 0;
    }

    public function intval($a)
    {
        return (int) $a;
    }

    public function strval($a)
    {
        return $a;
    }

    public function get($a)
    {
        return $a;
    }
}
