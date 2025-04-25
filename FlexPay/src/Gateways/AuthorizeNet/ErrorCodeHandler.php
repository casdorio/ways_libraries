<?php

namespace GatewayPayment\Gateways\AuthorizeNet;

use GatewayPayment\Exceptions\PaymentException;

class ErrorCodeHandler
{
    /**
     * Map AuthorizeNet error codes to custom exceptions or messages.
     *
     * @param mixed $response AuthorizeNet response object
     * @throws PaymentException
     */
    public function handle($response): void
    {
        if (!$response || !isset($response->transactionResponse)) {
            throw new PaymentException('Invalid or empty response from AuthorizeNet');
        }

        $responseCode = $response->transactionResponse->responseCode ?? null;
        $errorCode = $response->transactionResponse->errors[0]->errorCode ?? null;
        $errorText = $response->transactionResponse->errors[0]->errorText ?? 'Unknown error';

        if ($response->getMessages()->getResultCode() !== 'Ok' || $responseCode !== '1') {
            $this->mapErrorCode($errorCode, $errorText);
        }
    }

    /**
     * Map specific error codes to meaningful exceptions.
     *
     * @param string|null $errorCode
     * @param string $errorText
     * @throws PaymentException
     */
    private function mapErrorCode(?string $errorCode, string $errorText): void
    {
        $errorMap = [
            '2' => 'Transaction declined: Invalid card number',
            '3' => 'Transaction declined: Card expired',
            '6' => 'Transaction declined: Invalid CVV',
            '8' => 'Transaction declined: Card type not supported',
            '27' => 'AVS mismatch error',
            'E00027' => 'Payment declined: Authentication failure',
        ];

        $message = $errorMap[$errorCode] ?? "AuthorizeNet error: {$errorText} (Code: {$errorCode})";
        throw new PaymentException($message, $errorCode);
    }
}