<?php

namespace Casdorio\GatewayPayment\Core;

use Casdorio\GatewayPayment\Interfaces\PaymentGatewayInterface;
use Casdorio\GatewayPayment\Entities\Gateway;
use Casdorio\GatewayPayment\Gateways\AuthorizeNet\AuthorizeNetGateway;
use Casdorio\GatewayPayment\Gateways\Stripe\StripeGateway;
use Casdorio\GatewayPayment\Gateways\PayPal\PayPalGateway;
use Casdorio\GatewayPayment\Exceptions\ConfigurationException;

class PaymentGatewayFactory
{
    public static function create(Gateway $gateway, ?PaymentLogger $logger = null): PaymentGatewayInterface
    {
        switch ($gateway->type) {
            case 'authorize_net':
                return new AuthorizeNetGateway($gateway, $logger);
                
            case 'stripe':
                return new StripeGateway($gateway, $logger);
                
            case 'paypal':
                return new PayPalGateway($gateway, $logger);
                
            // Adicione mais gateways conforme necessário
                
            default:
                throw new ConfigurationException("Gateway de pagamento não suportado: {$gateway->type}");
        }
    }
}