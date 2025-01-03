<?php

namespace Casdorio\GatewayPayment\Services;

use Casdorio\GatewayPayment\Entities\PaymentGatewayType;
use Casdorio\GatewayPayment\Interfaces\PaymentGatewayInterface;
use Casdorio\GatewayPayment\Entities\Gateway;

class PaymentGatewayFactory
{
    public static function create(Gateway $gatewayEntity): PaymentGatewayInterface
    {
        // Verifica se o tipo de gateway é válido
        $gatewayType = PaymentGatewayType::from($gatewayEntity->name);

        // Obtém a classe correspondente do enum
        $gatewayClass = $gatewayType->getGatewayClass();

        // Instancia o gateway
        return new $gatewayClass($gatewayEntity);
    }
}