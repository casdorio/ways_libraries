<?php
declare(strict_types=1);

namespace Casdorio\CompactHash\Config;

final class Alphabet
{
    public const string DEFAULT = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    private const string SEPARATORS = 'cfhistuCFHISTU';
    private const float SEPARATOR_DIV = 3.5;
    private const int GUARD_DIV = 12;

    public static function shuffle(string $alphabet, string $salt): string
    {
        if (empty($salt)) {
            return $alphabet;
        }

        $saltLength = mb_strlen($salt);
        $alphabetArray = mb_str_split($alphabet);
        $saltArray = mb_str_split($salt);

        for ($i = count($alphabetArray) - 1, $v = 0, $p = 0; $i > 0; $i--, $v++) {
            $v %= $saltLength;
            $p += $int = ord($saltArray[$v]);
            $j = ($int + $v + $p) % $i;
            
            [$alphabetArray[$i], $alphabetArray[$j]] = [$alphabetArray[$j], $alphabetArray[$i]];
        }

        return implode('', $alphabetArray);
    }

    public static function getSeparators(string $alphabet): string
    {
        return implode('', array_intersect(
            mb_str_split(self::SEPARATORS),
            mb_str_split($alphabet)
        ));
    }

    public static function getGuards(string $alphabet, string $separators): string
    {
        $guards = str_replace(mb_str_split($separators), '', $alphabet);
        return $guards;
    }
}