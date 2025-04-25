<?php

namespace GatewayPayment\Gateways\PayPal;

use GatewayPayment\Entities\Payment;
use GatewayPayment\Entities\Customer;
use GatewayPayment\Entities\PaymentResponse;

class PayPalMapper
{
    /**
     * Map Payment entity to PayPal order data.
     *
     * @param Payment $payment
     * @return array
     */
    public function mapPaymentToOrder(Payment $payment): array
    {
        return [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => $payment->getCurrency(),
                        'value' => number_format($payment->getAmount(), 2, '.', ''),
                    ],
                    'description' => $payment->getDescription(),
                ],
            ],
            'payment_source' => [
                'card' => [
                    'number' => $payment->getCardInfo()->getCardNumber(),
                    'expiry' => sprintf(
                        '%d-%d',
                        $payment->getCardInfo()->getExpirationYear(),
                        $payment->getCardInfo()->getExpirationMonth()
                    ),
                    'security_code' => $payment->getCardInfo()->getCvv(),
                ],
            ],
        ];
    }

    /**
     * Map PayPal response to PaymentResponse entity.
     *
     * @param mixed $response
     * @return PaymentResponse
     */
    public function mapResponseToPaymentResponse($response): PaymentResponse
    {
        $paymentResponse = new PaymentResponse();
        $paymentResponse->setTransactionId($response->id);
        $paymentResponse->setStatus($response->status === 'COMPLETED' ? 'success' : $response->status);
        $paymentResponse->setAmount((float) $response->purchase_units[0]->amount->value);
        $paymentResponse->setCurrency($response->purchase_units[0]->amount->currency_code);
        $paymentResponse->setResponseData(json_encode((array) $response));

        return $paymentResponse;
    }

    /**
     * Map Customer entity to PayPal customer data (not fully supported).
     *
     * @param Customer $customer
     * @return array
     */
    public function mapCustomerToPayPal(Customer $customer): array
    {
        return [
            'email' => $customer->getEmail(),
            'name' => [
                'given_name' => explode(' ', $customer->getName())[0] ?? '',
                'surname' => explode(' ', $customer->getName())[1] ?? '',
            ],
        ];
    }
}