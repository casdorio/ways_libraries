<?php

namespace GatewayPayment\Gateways\Stripe;

use GatewayPayment\Exceptions\PaymentException;
use Stripe\Exception\ApiErrorException;

class ErrorCodeHandler
{
    /**
     * Handle Stripe API errors.
     *
     * @param ApiErrorException $exception
     * @throws PaymentException
     */
    public function handle(ApiErrorException $exception): void
    {
        $errorCode = $exception->getError()->code ?? 'unknown_error';
        $errorMessage = $exception->getError()->message ?? 'Unknown Stripe error';

        $errorMap = [
            'card_declined' => 'Transaction declined: Card was declined',
            'expired_card' => 'Transaction declined: Card has expired',
            'incorrect_cvc' => 'Transaction declined: Invalid CVV',
            'invalid_card_type' => 'Transaction declined: Card type not supported',
            'authentication_required' => 'Transaction requires authentication',
        ];

        $message = $errorMap[$errorCode] ?? "Stripe error: {$errorMessage} (Code: {$errorCode})";
        throw new PaymentException($message, $errorCode);
    }
}