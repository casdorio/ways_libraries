<?php

namespace GatewayPayment\Core;

use GatewayPayment\Interfaces\PaymentGatewayInterface;
use GatewayPayment\Exceptions\ConfigurationException;
use GatewayPayment\Exceptions\ValidationException;
use GatewayPayment\Entities\PaymentResponse;

abstract class BaseGateway implements PaymentGatewayInterface
{
    /**
     * Configurações do gateway
     * 
     * @var array
     */
    protected $config;
    
    /**
     * Logger para registrar transações e erros
     * 
     * @var \GatewayPayment\Core\PaymentLogger
     */
    protected $logger;
    
    /**
     * Nome do gateway
     * 
     * @var string
     */
    protected $gatewayName;
    
    /**
     * Ambiente (sandbox ou production)
     * 
     * @var string
     */
    protected $environment;
    
    /**
     * Construtor
     * 
     * @param array $config Configurações do gateway
     * @param \GatewayPayment\Core\PaymentLogger|null $logger Logger opcional
     */
    public function __construct(array $config, ?PaymentLogger $logger = null)
    {
        $this->validateConfig($config);
        $this->config = $config;
        $this->environment = $config['environment'] ?? 'sandbox';
        $this->logger = $logger ?? new PaymentLogger();
    }
    
    /**
     * Valida as configurações do gateway
     * 
     * @param array $config Configurações a serem validadas
     * @throws \GatewayPayment\Exceptions\ConfigurationException Se as configurações forem inválidas
     */
    abstract protected function validateConfig(array $config);
    
    /**
     * Formata o valor monetário para o formato padrão
     * 
     * @param float $amount Valor a ser formatado
     * @return string Valor formatado
     */
    protected function formatAmount(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }
    
    /**
     * Gera um ID de referência único
     * 
     * @param string $prefix Prefixo opcional para o ID
     * @return string ID de referência
     */
    protected function generateReferenceId(string $prefix = 'txn'): string
    {
        return $prefix . '_' . uniqid() . '_' . time();
    }
    
    /**
     * Valida os dados básicos para uma transação
     * 
     * @param array $data Dados a serem validados
     * @param array $requiredFields Campos obrigatórios
     * @throws \GatewayPayment\Exceptions\ValidationException Se os dados forem inválidos
     */
    protected function validateRequiredFields(array $data, array $requiredFields)
    {
        $missingFields = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
                $missingFields[] = $field;
            }
        }
        
        if (!empty($missingFields)) {
            throw new ValidationException('Campos obrigatórios não fornecidos: ' . implode(', ', $missingFields));
        }
    }
    
    /**
     * Registra uma transação no log
     * 
     * @param string $action Ação realizada
     * @param array $data Dados da transação
     * @param \GatewayPayment\Entities\PaymentResponse $response Resposta da transação
     */
    protected function logTransaction(string $action, array $data, PaymentResponse $response): void
    {
        // Remover dados sensíveis antes de registrar
        $sanitizedData = $this->sanitizeDataForLogging($data);
        
        $this->logger->log(
            $this->gatewayName,
            $action,
            $sanitizedData,
            [
                'success' => $response->isSuccessful(),
                'transaction_id' => $response->getTransactionId(),
                'message' => $response->getMessage(),
                'error_code' => $response->getErrorCode()
            ]
        );
    }
    
    /**
     * Remove dados sensíveis antes de registrar
     * 
     * @param array $data Dados a serem sanitizados
     * @return array Dados sanitizados
     */
    protected function sanitizeDataForLogging(array $data): array
    {
        $sensitiveFields = ['card_number', 'cvv', 'password', 'token', 'access_token'];
        $sanitized = $data;
        
        foreach ($sensitiveFields as $field) {
            if (isset($sanitized[$field])) {
                $sanitized[$field] = '***' . substr($sanitized[$field], -4);
            }
        }
        
        return $sanitized;
    }
}