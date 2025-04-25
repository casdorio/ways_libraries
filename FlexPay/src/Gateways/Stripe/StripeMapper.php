<?php

namespace GatewayPayment\Gateways\Stripe;

use GatewayPayment\Entities\Payment;
use GatewayPayment\Entities\Customer;
use GatewayPayment\Entities\PaymentResponse;

class StripeMapper
{
    /**
     * Map Payment entity to Stripe charge data.
     *
     * @param Payment $payment
     * @return array
     */
    public function mapPaymentToCharge(Payment $payment): array
    {
        return [
            'amount' => (int) ($payment->getAmount() * 100), // Convert to cents
            'currency' => $payment->getCurrency(),
            'source' => $payment->getCardInfo()->getToken() ?: [
                'number' => $payment->getCardInfo()->getCardNumber(),
                'exp_month' => $payment->getCardInfo()->getExpirationMonth(),
                'exp_year' => $payment->getCardInfo()->getExpirationYear(),
                'cvc' => $payment->getCardInfo()->getCvv(),
            ],
            'description' => $payment->getDescription(),
            'customer' => $payment->getCustomerId(),
        ];
    }

    /**
     * Map Stripe response to PaymentResponse entity.
     *
     * @param mixed $response
     * @return PaymentResponse
     */
    public function mapResponseToPaymentResponse($response): PaymentResponse
    {
        $paymentResponse = new PaymentResponse();
        $paymentResponse->setTransactionId($response->id);
        $paymentResponse->setStatus($response->status === 'succeeded' ? 'success' : $response->status);
        $paymentResponse->setAmount($response->amount / 100); // Convert from cents
        $paymentResponse->setCurrency($response->currency);
        $paymentResponse->setResponseData(json_encode($response->toArray()));

        return $paymentResponse;
    }

    /**
     * Map Customer entity to Stripe customer data.
     *
     * @param Customer $customer
     * @return array
     */
    public function mapCustomerToStripe(Customer $customer): array
    {
        $data = [
            'email' => $customer->getEmail(),
            'name' => $customer->getName(),
        ];

        if ($cardInfo = $customer->getCardInfo()) {
            $data['source'] = [
                'number' => $cardInfo->getCardNumber(),
                'exp_month' => $cardInfo->getExpirationMonth(),
                'exp_year' => $cardInfo->getExpirationYear(),
                'cvc' => $cardInfo->getCvv(),
            ];
        }

        return $data;
    }
}