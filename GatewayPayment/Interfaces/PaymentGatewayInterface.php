<?php

namespace Casdorio\GatewayPayment\Interfaces;

use Casdorio\GatewayPayment\Entities\Payment;

interface PaymentGatewayInterface
{
    public function chargeCreditCard(Payment $payment);
    public function authorize(Payment $payment);
    public function refund(Payment $payment, $transactionId);
    public function void($transactionId);
    public function capture($transactionId, $amount);
    public function getAnAcceptPaymentPage($transactionId, $amount);
    public function getTransactionDetails($transactionId);
}