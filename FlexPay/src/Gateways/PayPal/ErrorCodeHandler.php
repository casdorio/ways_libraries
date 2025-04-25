<?php

namespace GatewayPayment\Gateways\PayPal;

use GatewayPayment\Exceptions\PaymentException;

class ErrorCodeHandler
{
    /**
     * Handle PayPal API errors.
     *
     * @param \Exception $exception
     * @throws PaymentException
     */
    public function handle(\Exception $exception): void
    {
        $errorCode = $exception->getCode() ?: 'unknown_error';
        $errorMessage = $exception->getMessage() ?: 'Unknown PayPal error';

        $errorMap = [
            'PAYMENT_DENIED' => 'Transaction declined: Payment was denied',
            'CARD_EXPIRED' => 'Transaction declined: Card has expired',
            'INVALID_CVC' => 'Transaction declined: Invalid CVV',
            'UNSUPPORTED_CARD_TYPE' => 'Transaction declined: Card type not supported',
        ];

        $message = $errorMap[$errorCode] ?? "PayPal error: {$errorMessage} (Code: {$errorCode})";
        throw new PaymentException($message, $errorCode);
    }
}