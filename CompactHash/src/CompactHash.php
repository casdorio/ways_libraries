<?php
declare(strict_types=1);

namespace Casdorio\CompactHash;

use Casdorio\CompactHash\Config\{Alphabet, Settings};
use Casdorio\CompactHash\Exception\{InvalidHashException, InvalidNumberException};
use Casdorio\CompactHash\Math\BigMath;

final readonly class CompactHash implements CompactHashInterface
{
    private string $alphabet;
    private string $separators;
    private string $guards;

    public function __construct(
        private Settings $settings = new Settings()
    ) {
        $this->initializeAlphabet();
    }

    public function encode(array $numbers): string
    {
        $numbers = $this->normalizeAndValidateNumbers($numbers);
        return empty($numbers) ? '' : $this->hashNumbers($numbers);
    }

    public function decode(string $hash): array
    {
        $this->validateHash($hash);
        return $hash === '' ? [] : $this->unhashNumbers($hash);
    }

    private function initializeAlphabet(): void
    {
        $this->alphabet = Alphabet::shuffle($this->settings->alphabet, $this->settings->salt);
        $this->separators = Alphabet::getSeparators($this->alphabet);
        $this->guards = Alphabet::getGuards($this->alphabet, $this->separators);
    }

    private function hashNumbers(array $numbers): string
    {
        $alphabet = $this->alphabet;
        $numbersHash = '0';
        
        foreach ($numbers as $i => $number) {
            $numbersHash = BigMath::add($numbersHash, BigMath::mod($number, (string)($i + 100)));
        }

        $lottery = $alphabet[(int)BigMath::mod($numbersHash, (string)mb_strlen($alphabet))];
        $result = [$lottery];

        foreach ($numbers as $i => $number) {
            $alphabet = $this->shuffleAlphabet($alphabet, $lottery);
            $last = $this->hashNumber($number, $alphabet);
            $result[] = $last;

            if ($i + 1 < count($numbers)) {
                $separatorIndex = (int)BigMath::mod(
                    BigMath::mod($number, (string)(ord($last[0]) + $i)),
                    (string)mb_strlen($this->separators)
                );
                $result[] = $this->separators[$separatorIndex];
            }
        }

        $resultStr = implode('', $result);
        return mb_strlen($resultStr) < $this->settings->minHashLength
            ? $this->padHash($resultStr, $numbersHash)
            : $resultStr;
    }

    private function unhashNumbers(string $hash): array
    {
        $alphabet = $this->alphabet;
        $hashArray = mb_str_split($hash);
        $lottery = $hashArray[0];
        $hash = implode('', array_slice($hashArray, 1));

        $hashParts = preg_split(
            '/[' . preg_quote($this->guards, '/') . ']/',
            $hash,
            2
        ) ?: [''];

        $numbers = [];
        foreach (explode($hashParts[0][0] ?? ' ', $hashParts[0]) as $subHash) {
            $alphabet = $this->shuffleAlphabet($alphabet, $lottery);
            $numbers[] = $this->unhashNumber($subHash, $alphabet);
        }

        return $numbers;
    }

    private function hashNumber(string $number, string $alphabet): string
    {
        $result = [];
        $alphabetLength = mb_strlen($alphabet);

        do {
            $result[] = $alphabet[(int)BigMath::mod($number, (string)$alphabetLength)];
            $number = BigMath::div($number, (string)$alphabetLength);
        } while (BigMath::cmp($number, '0') > 0);

        return implode('', array_reverse($result));
    }

    private function unhashNumber(string $hash, string $alphabet): string
    {
        $number = '0';
        $alphabetLength = mb_strlen($alphabet);

        foreach (mb_str_split($hash) as $char) {
            $pos = mb_strpos($alphabet, $char);
            if ($pos === false) {
                throw new InvalidHashException("Invalid character in hash");
            }
            $number = BigMath::add(BigMath::mul($number, (string)$alphabetLength), (string)$pos);
        }

        return $number;
    }

    private function padHash(string $hash, string $numbersHash): string
    {
        $guardIndex = (int)BigMath::mod($numbersHash, (string)mb_strlen($this->guards));
        $hash = $this->guards[$guardIndex] . $hash;

        if (mb_strlen($hash) < $this->settings->minHashLength) {
            $guardIndex = (int)BigMath::mod(
                BigMath::add($numbersHash, (string)ord($hash[0])),
                (string)mb_strlen($this->guards)
            );
            $hash .= $this->guards[$guardIndex];
        }

        return $hash;
    }

    private function shuffleAlphabet(string $alphabet, string $lottery): string
    {
        return Alphabet::shuffle($alphabet, $lottery . $this->settings->salt . $alphabet);
    }

    /**
     * @return array<string>
     */
    private function normalizeAndValidateNumbers(array $numbers): array
    {
        return array_map(function($num) {
            if (is_int($num)) {
                if ($num < 0) throw new InvalidNumberException("Negative numbers not allowed");
                return (string)$num;
            }
            
            if (!is_string($num) || !ctype_digit($num)) {
                throw new InvalidNumberException("Numbers must be positive integers or numeric strings");
            }
            
            return $num;
        }, $numbers);
    }

    private function validateHash(string $hash): void
    {
        if ($hash === '') return;

        $validChars = $this->alphabet . $this->guards . $this->separators;
        foreach (mb_str_split($hash) as $char) {
            if (mb_strpos($validChars, $char) === false) {
                throw new InvalidHashException("Invalid character in hash");
            }
        }
    }
}