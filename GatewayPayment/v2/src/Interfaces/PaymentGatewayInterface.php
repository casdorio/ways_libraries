<?php

namespace Casdorio\GatewayPayment\Interfaces;

use Casdorio\GatewayPayment\Entities\Payment;
use Casdorio\GatewayPayment\Entities\PaymentResponse;
use Casdorio\GatewayPayment\Entities\Customer;

interface PaymentGatewayInterface
{
    // Métodos obrigatórios para todos os gateways
    public function chargeCreditCard(Payment $payment): PaymentResponse;
    public function authorize(Payment $payment): PaymentResponse;
    public function capture(string $transactionId, float $amount): PaymentResponse;
    public function void(string $transactionId): PaymentResponse;
    public function refund(string $transactionId, float $amount): PaymentResponse;
    
    // Métodos opcionais
    public function getTransactionDetails(string $transactionId): PaymentResponse;
    
    // Métodos relacionados a perfis de cliente (podem ser movidos para uma interface separada)
    public function createCustomerProfile(Customer $customer): ?PaymentResponse;
    public function chargeCustomerProfile(string $profileId, float $amount): ?PaymentResponse;
}