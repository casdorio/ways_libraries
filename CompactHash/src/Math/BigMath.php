<?php
declare(strict_types=1);

namespace Casdorio\CompactHash\Math;

final class BigMath
{
    private static bool $gmp;
    private static bool $bcmath;

    public static function init(): void
    {
        self::$gmp = extension_loaded('gmp');
        self::$bcmath = extension_loaded('bcmath');
    }

    public static function add(string $a, string $b): string
    {
        return self::$gmp 
            ? gmp_strval(gmp_add($a, $b)) 
            : (self::$bcmath ? bcadd($a, $b, 0) : (string)((int)$a + (int)$b));
    }

    public static function sub(string $a, string $b): string
    {
        return self::$gmp 
            ? gmp_strval(gmp_sub($a, $b)) 
            : (self::$bcmath ? bcsub($a, $b, 0) : (string)((int)$a - (int)$b));
    }

    public static function mul(string $a, string $b): string
    {
        return self::$gmp 
            ? gmp_strval(gmp_mul($a, $b)) 
            : (self::$bcmath ? bcmul($a, $b, 0) : (string)((int)$a * (int)$b));
    }

    public static function div(string $a, string $b): string
    {
        return self::$gmp 
            ? gmp_strval(gmp_div_q($a, $b)) 
            : (self::$bcmath ? bcdiv($a, $b, 0) : (string)((int)$a / (int)$b));
    }

    public static function mod(string $a, string $b): string
    {
        return self::$gmp 
            ? gmp_strval(gmp_mod($a, $b)) 
            : (self::$bcmath ? bcmod($a, $b) : (string)((int)$a % (int)$b));
    }

    public static function cmp(string $a, string $b): int
    {
        return self::$gmp 
            ? gmp_cmp($a, $b) 
            : (self::$bcmath ? bccomp($a, $b, 0) : $a <=> $b);
    }
}

BigMath::init();