<?php

namespace Ways\GatewayPayment\Core;

use Ways\GatewayPayment\Exceptions\ConfigurationException;
use Ways\GatewayPayment\Interfaces\PaymentGatewayInterface;
use Ways\GatewayPayment\Gateways\AuthorizeNet\AuthorizeNetGateway;
use Ways\GatewayPayment\Gateways\Stripe\StripeGateway;
use Ways\GatewayPayment\Gateways\PayPal\PayPalGateway;

class PaymentGatewayFactory
{
    /**
     * Cria uma instância do gateway com base no nome
     * 
     * @param string $gatewayName Nome do gateway
     * @param array $config Configurações do gateway
     * @param \Ways\GatewayPayment\Core\PaymentLogger|null $logger Logger opcional
     * @return \Ways\GatewayPayment\Interfaces\PaymentGatewayInterface
     * @throws \Ways\GatewayPayment\Exceptions\ConfigurationException Se o gateway não for suportado
     */
    public static function create(string $gatewayName, array $config = [], ?PaymentLogger $logger = null): PaymentGatewayInterface
    {
        $configManager = new GatewayConfigManager();
        
        // Mesclar configurações personalizadas com as padrões
        if (empty($config)) {
            $config = $configManager->getGatewayConfig($gatewayName);
        } else {
            $defaultConfig = $configManager->getGatewayConfig($gatewayName);
            $config = array_merge($defaultConfig, $config);
        }
        
        switch (strtolower($gatewayName)) {
            case 'authorize.net':
                return new AuthorizeNetGateway($config, $logger);
                
            case 'stripe':
                return new StripeGateway($config, $logger);
                
            case 'paypal':
                return new PayPalGateway($config, $logger);
                
            default:
                throw new ConfigurationException("Gateway não suportado: {$gatewayName}");
        }
    }
    
    /**
     * Lista todos os gateways disponíveis
     * 
     * @return array Lista de gateways
     */
    public static function getAvailableGateways(): array
    {
        return [
            'authorize.net' => 'Authorize.Net',
            'stripe' => 'Stripe',
            'paypal' => 'PayPal'
        ];
    }
}