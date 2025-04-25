<?php
declare(strict_types=1);

namespace Casdorio\CompactHash\Test\Unit;

use PHPUnit\Framework\TestCase;
use Casdorio\CompactHash\CompactHash;
use Casdorio\CompactHash\Config\Settings;
use Casdorio\CompactHash\Exception\{
    InvalidHashException,
    InvalidNumberException
};

final class CompactHashTest extends TestCase
{
    private CompactHash $hasher;
    private CompactHash $customHasher;

    protected function setUp(): void
    {
        $this->hasher = new CompactHash();
        $this->customHasher = new CompactHash(new Settings(
            salt: 'my_special_salt',
            minHashLength: 10,
            alphabet: 'abcdefghijklmnopqrstuvwxyz'
        ));
    }

    public function testEncodeDecodeBasic(): void
    {
        $numbers = [1, 2, 3];
        $hash = $this->hasher->encode($numbers);
        $decoded = $this->hasher->decode($hash);

        $this->assertIsString($hash);
        $this->assertNotEmpty($hash);
        $this->assertEquals($numbers, array_map('intval', $decoded));
    }

    public function testEncodeDecodeWithLargeNumbers(): void
    {
        $largeNumbers = [
            '12345678901234567890',
            '98765432109876543210',
            '18446744073709551615' // 2^64 - 1
        ];

        $hash = $this->hasher->encode($largeNumbers);
        $decoded = $this->hasher->decode($hash);

        $this->assertEquals($largeNumbers, $decoded);
    }

    public function testEncodeDecodeWithMixedNumbers(): void
    {
        $mixedNumbers = [42, '12345678901234567890', 7];
        $hash = $this->hasher->encode($mixedNumbers);
        $decoded = $this->hasher->decode($hash);

        $expected = ['42', '12345678901234567890', '7'];
        $this->assertEquals($expected, $decoded);
    }

    public function testCustomAlphabet(): void
    {
        $numbers = [1, 2, 3];
        $hash = $this->customHasher->encode($numbers);
        
        // Verifica se só contém letras minúsculas
        $this->assertMatchesRegularExpression('/^[a-z]+$/', $hash);
        $this->assertGreaterThanOrEqual(10, strlen($hash));
        
        $decoded = $this->customHasher->decode($hash);
        $this->assertEquals($numbers, array_map('intval', $decoded));
    }

    public function testEmptyArray(): void
    {
        $this->assertEquals('', $this->hasher->encode([]));
        $this->assertEquals([], $this->hasher->decode(''));
    }

    public function testMinHashLength(): void
    {
        $hasher = new CompactHash(new Settings(minHashLength: 20));
        $hash = $hasher->encode([1]);
        
        $this->assertGreaterThanOrEqual(20, strlen($hash));
        $this->assertEquals(['1'], $hasher->decode($hash));
    }

    public function testSaltMakesDifference(): void
    {
        $hasher1 = new CompactHash(new Settings(salt: 'salt1'));
        $hasher2 = new CompactHash(new Settings(salt: 'salt2'));
        
        $hash1 = $hasher1->encode([1, 2, 3]);
        $hash2 = $hasher2->encode([1, 2, 3]);
        
        $this->assertNotEquals($hash1, $hash2);
    }

    public function testConsistency(): void
    {
        $numbers = [42, 1337, 999];
        $hash1 = $this->hasher->encode($numbers);
        $hash2 = $this->hasher->encode($numbers);
        
        $this->assertEquals($hash1, $hash2);
        $this->assertEquals($numbers, array_map('intval', $this->hasher->decode($hash1)));
    }

    public function testInvalidNumbers(): void
    {
        $this->expectException(InvalidNumberException::class);
        $this->hasher->encode([-1]);
    }

    public function testInvalidNumberStrings(): void
    {
        $this->expectException(InvalidNumberException::class);
        $this->hasher->encode(['not_a_number']);
    }

    public function testInvalidHash(): void
    {
        $this->expectException(InvalidHashException::class);
        $this->hasher->decode('invalid$hash#');
    }

    public function testVeryLargeSingleNumber(): void
    {
        $veryLarge = '9' . str_repeat('0', 100); // 10^100
        $hash = $this->hasher->encode([$veryLarge]);
        $decoded = $this->hasher->decode($hash);
        
        $this->assertEquals([$veryLarge], $decoded);
    }

    public function testMultipleLargeNumbers(): void
    {
        $numbers = [
            '123456789012345678901234567890',
            '987654321098765432109876543210',
            '111111111111111111111111111111'
        ];
        
        $hash = $this->hasher->encode($numbers);
        $decoded = $this->hasher->decode($hash);
        
        $this->assertEquals($numbers, $decoded);
    }

    public function testAlphabetUniqueness(): void
    {
        $hasher = new CompactHash(new Settings(alphabet: 'aabbcc'));
        $this->expectException(\InvalidArgumentException::class);
        $hasher->encode([1]);
    }

    public function testSpecialCharactersInAlphabet(): void
    {
        $hasher = new CompactHash(new Settings(alphabet: 'abc123$%^'));
        $hash = $hasher->encode([1, 2, 3]);
        
        $this->assertMatchesRegularExpression('/^[abc123\$\%\^]+$/', $hash);
        $this->assertEquals(['1', '2', '3'], $hasher->decode($hash));
    }

    public function testZeroAndVerySmallNumbers(): void
    {
        $numbers = [0, 1, 2];
        $hash = $this->hasher->encode($numbers);
        $decoded = $this->hasher->decode($hash);
        
        $this->assertEquals($numbers, array_map('intval', $decoded));
    }

    public function testMaxIntNumbers(): void
    {
        $maxInt = (string)PHP_INT_MAX;
        $numbers = [$maxInt, $maxInt];
        $hash = $this->hasher->encode($numbers);
        $decoded = $this->hasher->decode($hash);
        
        $this->assertEquals($numbers, $decoded);
    }

    public function testRepeatabilityWithCustomSalt(): void
    {
        $settings = new Settings(salt: 'fixed_salt');
        $hasher1 = new CompactHash($settings);
        $hasher2 = new CompactHash($settings);
        
        $numbers = [42, 1337, 99];
        $hash1 = $hasher1->encode($numbers);
        $hash2 = $hasher2->encode($numbers);
        
        $this->assertEquals($hash1, $hash2);
    }
}