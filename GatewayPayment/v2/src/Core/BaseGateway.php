<?php

namespace Casdorio\GatewayPayment\Core;

use Casdorio\GatewayPayment\Interfaces\PaymentGatewayInterface;
use Casdorio\GatewayPayment\Entities\Gateway;
use Casdorio\GatewayPayment\Entities\PaymentResponse;
use Casdorio\GatewayPayment\Entities\Customer;
use Casdorio\GatewayPayment\Entities\Payment;
use Casdorio\GatewayPayment\Exceptions\PaymentException;

abstract class BaseGateway implements PaymentGatewayInterface
{
    protected $gateway;
    protected $logger;
    
    public function __construct(Gateway $gateway, ?PaymentLogger $logger = null)
    {
        $this->gateway = $gateway;
        $this->logger = $logger ?? new PaymentLogger();
    }
    
    // Método utilitário para formatar o valor monetário
    protected function formatAmount(float $amount): float
    {
        return round($amount, 2);
    }
    
    // Método utilitário para criar uma resposta padronizada
    protected function createPaymentResponse(bool $success, ?string $transactionId = null, ?string $errorMessage = null, $rawResponse = null): PaymentResponse
    {
        return new PaymentResponse($success, $transactionId, $errorMessage, $rawResponse);
    }
    
    // Método para log de transações
    protected function logTransaction(string $transactionType, float $amount, ?string $transactionId, bool $success): void
    {
        if ($this->logger) {
            $this->logger->logTransaction(
                get_class($this),
                $transactionType,
                $amount,
                $transactionId,
                $success
            );
        }
    }
    
    // Método para log de erros
    protected function logError(string $errorMessage, $rawError = null): void
    {
        if ($this->logger) {
            $this->logger->logError(
                get_class($this),
                $errorMessage,
                $rawError
            );
        }
    }
    
    // Implementações padrão dos métodos opcionais
    public function createCustomerProfile(Customer $customer): ?PaymentResponse
    {
        // Por padrão, gateways podem não suportar perfis de cliente
        return $this->createPaymentResponse(false, null, "Este gateway não suporta perfis de cliente");
    }
    
    public function chargeCustomerProfile(string $profileId, float $amount): ?PaymentResponse
    {
        // Por padrão, gateways podem não suportar perfis de cliente
        return $this->createPaymentResponse(false, null, "Este gateway não suporta cobrança via perfil de cliente");
    }
    
    public function getTransactionDetails(string $transactionId): PaymentResponse
    {
        // Por padrão, gateways podem não suportar consulta de detalhes
        return $this->createPaymentResponse(false, null, "Este gateway não suporta consulta de detalhes de transação");
    }
}