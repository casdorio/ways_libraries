<?php
declare(strict_types=1);

namespace Casdorio\CompactHash\Test\Unit;

use PHPUnit\Framework\TestCase;
use Casdorio\CompactHash\Config\Alphabet;

final class AlphabetTest extends TestCase
{
    public function testShuffleWithSalt(): void
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyz';
        $salt = 'my_salt';
        
        $shuffled1 = Alphabet::shuffle($alphabet, $salt);
        $shuffled2 = Alphabet::shuffle($alphabet, $salt);
        
        $this->assertEquals($shuffled1, $shuffled2);
        $this->assertNotEquals($alphabet, $shuffled1);
        $this->assertEquals(strlen($alphabet), strlen($shuffled1));
        $this->assertEmpty(array_diff(
            str_split($alphabet),
            str_split($shuffled1)
        ));
    }

    public function testShuffleWithDifferentSalt(): void
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyz';
        $shuffled1 = Alphabet::shuffle($alphabet, 'salt1');
        $shuffled2 = Alphabet::shuffle($alphabet, 'salt2');
        
        $this->assertNotEquals($shuffled1, $shuffled2);
    }

    public function testGetSeparators(): void
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzCFHISTU1234567890';
        $separators = Alphabet::getSeparators($alphabet);
        
        $expected = 'cfhistuCFHISTU';
        $this->assertEquals(
            str_split($expected),
            str_split($separators)
        );
    }

    public function testGetGuards(): void
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzCFHISTU1234567890';
        $separators = 'cfhistuCFHISTU';
        $guards = Alphabet::getGuards($alphabet, $separators);
        
        $expected = 'abdegjklmnopqrvwxyz1234567890';
        $this->assertEquals(
            str_split($expected),
            str_split($guards)
        );
    }
}