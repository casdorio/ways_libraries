<?php

namespace Ways\GatewayPayment\Core;

use Psr\Log\LoggerInterface;

class PaymentLogger
{
    /**
     * Logger PSR-3
     * 
     * @var \Psr\Log\LoggerInterface|null
     */
    private $logger;
    
    /**
     * Caminho para o arquivo de log
     * 
     * @var string|null
     */
    private $logPath;
    
    /**
     * Construtor
     * 
     * @param \Psr\Log\LoggerInterface|null $logger Logger opcional
     * @param string|null $logPath Caminho para o arquivo de log
     */
    public function __construct(?LoggerInterface $logger = null, ?string $logPath = null)
    {
        $this->logger = $logger;
        $this->logPath = $logPath ?? dirname(__DIR__, 2) . '/logs/payments.log';
        
        // Criar diretório de logs se não existir
        if (!is_null($this->logPath)) {
            $logDir = dirname($this->logPath);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
        }
    }
    
    /**
     * Registra uma ação de pagamento no log
     * 
     * @param string $gateway Nome do gateway
     * @param string $action Ação realizada
     * @param array $requestData Dados da requisição
     * @param array $responseData Dados da resposta
     */
    public function log(string $gateway, string $action, array $requestData = [], array $responseData = []): void
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'gateway' => $gateway,
            'action' => $action,
            'request' => $requestData,
            'response' => $responseData
        ];
        
        // Usar o logger PSR-3 se disponível
        if ($this->logger) {
            $level = $responseData['success'] ?? true ? 'info' : 'error';
            $this->logger->log($level, "Payment {$action} via {$gateway}", $logEntry);
            return;
        }
        
        // Caso contrário, registrar no arquivo de log
        if (!is_null($this->logPath)) {
            $logJson = json_encode($logEntry, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            file_put_contents(
                $this->logPath,
                "[{$logEntry['timestamp']}] {$gateway} {$action}" . PHP_EOL . $logJson . PHP_EOL . PHP_EOL,
                FILE_APPEND
            );
        }
    }
    
    /**
     * Define um logger PSR-3
     * 
     * @param \Psr\Log\LoggerInterface $logger Logger
     * @return self
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }
    
    /**
     * Define o caminho para o arquivo de log
     * 
     * @param string $logPath Caminho para o arquivo de log
     * @return self
     */
    public function setLogPath(string $logPath): self
    {
        $this->logPath = $logPath;
        return $this;
    }
}