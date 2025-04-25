<?php
declare(strict_types=1);

namespace Casdorio\CompactHash\Test\Unit;

use PHPUnit\Framework\TestCase;
use Casdorio\CompactHash\Math\BigMath;

final class BigMathTest extends TestCase
{
    public function testAdd(): void
    {
        $this->assertEquals('12345678901234567890', BigMath::add('12345678901234567800', '90'));
        $this->assertEquals('200', BigMath::add('100', '100'));
    }

    public function testSub(): void
    {
        $this->assertEquals('12345678901234567800', BigMath::sub('12345678901234567890', '90'));
        $this->assertEquals('0', BigMath::sub('100', '100'));
    }

    public function testMul(): void
    {
        $this->assertEquals('24691357802469135600', BigMath::mul('12345678901234567800', '2'));
        $this->assertEquals('10000', BigMath::mul('100', '100'));
    }

    public function testDiv(): void
    {
        $this->assertEquals('6172839450617283900', BigMath::div('12345678901234567800', '2'));
        $this->assertEquals('1', BigMath::div('100', '100'));
    }

    public function testMod(): void
    {
        $this->assertEquals('0', BigMath::mod('12345678901234567800', '2'));
        $this->assertEquals('10', BigMath::mod('12345678901234567890', '20'));
    }

    public function testCmp(): void
    {
        $this->assertEquals(1, BigMath::cmp('12345678901234567890', '1'));
        $this->assertEquals(-1, BigMath::cmp('1', '12345678901234567890'));
        $this->assertEquals(0, BigMath::cmp('12345678901234567890', '12345678901234567890'));
    }

    public function testVeryLargeNumbers(): void
    {
        $veryLarge = '9' . str_repeat('0', 1000); // 10^1000
        $one = '1';
        
        $this->assertEquals($veryLarge . '1', BigMath::add($veryLarge, $one));
        $this->assertEquals('8' . str_repeat('9', 1000), BigMath::sub($veryLarge, $one));
    }
}