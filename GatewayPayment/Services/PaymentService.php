<?php

namespace Casdorio\GatewayPayment\Services;

use Casdorio\GatewayPayment\Interfaces\PaymentGatewayInterface;
use Casdorio\GatewayPayment\Entities\Payment;

class PaymentService
{
    protected PaymentGatewayInterface $gateway;

    public function __construct(PaymentGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    public function chargeCreditCard(Payment $payment)
    {
        return $this->gateway->chargeCreditCard($payment);
    }

    public function authorize(Payment $payment)
    {
        return $this->gateway->authorize($payment);
    }

    public function refund(Payment $payment, string $transactionId)
    {
        return $this->gateway->refund($payment, $transactionId);
    }

    public function void(string $transactionId)
    {
        return $this->gateway->void($transactionId);
    }

    public function capture(string $transactionId, float $amount)
    {
        return $this->gateway->capture($transactionId, $amount);
    }

    public function getAnAcceptPaymentPage(string $transactionId, float $amount)
    {
        return $this->gateway->getAnAcceptPaymentPage($transactionId, $amount);
    }

    public function getTransactionDetails(string $transactionId)
    {
        return $this->gateway->getTransactionDetails($transactionId);
    }
}