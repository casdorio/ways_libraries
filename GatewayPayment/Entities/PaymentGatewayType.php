<?php

namespace Casdorio\GatewayPayment\Entities;

use Casdorio\GatewayPayment\Gateways\AuthorizeNet\AuthorizeNetGateway;
use Casdorio\GatewayPayment\Gateways\Stripe\StripeGateway;

enum PaymentGatewayType: string
{
    case STRIPE = 'stripe';
    case AUTHORIZE_NET = 'authorize_net';

    public function getGatewayClass(): string
    {
        return match ($this) {
            self::STRIPE => StripeGateway::class,
            self::AUTHORIZE_NET => AuthorizeNetGateway::class,
        };
    }
}