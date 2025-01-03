<?php

namespace App\Libraries\GatewayPayment\Services;

use App\Libraries\GatewayPayment\Entities\PaymentGatewayType;
use App\Libraries\GatewayPayment\Interfaces\PaymentGatewayInterface;
use App\Libraries\GatewayPayment\Entities\Gateway;

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