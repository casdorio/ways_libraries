<?php
declare(strict_types=1);

namespace Casdorio\CompactHash\Test\Integration;

use PHPUnit\Framework\TestCase;
use Casdorio\CompactHash\CompactHash;
use Casdorio\CompactHash\Config\Settings;

final class CompactHashIntegrationTest extends TestCase
{
    public function testFullIntegration(): void
    {
        $settings = new Settings(
            salt: 'integration_test_salt',
            minHashLength: 12,
            alphabet: 'abcdefghijklmnopqrstuvwxyz1234567890'
        );
        
        $hasher = new CompactHash($settings);
        
        // Teste com diferentes tipos de números
        $testCases = [
            [1, 2, 3],
            ['12345678901234567890', '98765432109876543210'],
            [PHP_INT_MAX, PHP_INT_MAX - 1],
            [0, 1, 999999],
            ['1' . str_repeat('0', 100)] // 10^100
        ];
        
        foreach ($testCases as $numbers) {
            $hash = $hasher->encode($numbers);
            
            // Verifica o comprimento mínimo
            $this->assertGreaterThanOrEqual(12, strlen($hash));
            
            // Verifica o alfabeto customizado
            $this->assertMatchesRegularExpression('/^[a-z0-9]+$/', $hash);
            
            // Verifica a decodificação
            $decoded = $hasher->decode($hash);
            $this->assertEquals($numbers, array_map('strval', $decoded));
        }
    }
}