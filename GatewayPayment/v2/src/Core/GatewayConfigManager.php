<?php

namespace Ways\GatewayPayment\Core;

use Ways\GatewayPayment\Exceptions\ConfigurationException;

class GatewayConfigManager
{
    /**
     * Configurações dos gateways
     * 
     * @var array
     */
    private $config;
    
    /**
     * Construtor
     */
    public function __construct()
    {
        $configPath = dirname(__DIR__, 2) . '/config/gateway_config.php';
        
        if (file_exists($configPath)) {
            $this->config = require $configPath;
        } else {
            $this->config = [];
        }
    }
    
    /**
     * Obtém as configurações de um gateway específico
     * 
     * @param string $gatewayName Nome do gateway
     * @return array Configurações do gateway
     * @throws \Ways\GatewayPayment\Exceptions\ConfigurationException Se o gateway não estiver configurado
     */
    public function getGatewayConfig(string $gatewayName): array
    {
        $gatewayKey = strtolower($gatewayName);
        
        if (!isset($this->config[$gatewayKey])) {
            throw new ConfigurationException("Gateway não configurado: {$gatewayName}");
        }
        
        return $this->config[$gatewayKey];
    }
    
    /**
     * Define as configurações de um gateway
     * 
     * @param string $gatewayName Nome do gateway
     * @param array $config Configurações do gateway
     * @return self
     */
    public function setGatewayConfig(string $gatewayName, array $config): self
    {
        $gatewayKey = strtolower($gatewayName);
        $this->config[$gatewayKey] = $config;
        return $this;
    }
    
    /**
     * Verifica se um gateway está configurado
     * 
     * @param string $gatewayName Nome do gateway
     * @return bool True se estiver configurado, False caso contrário
     */
    public function hasGatewayConfig(string $gatewayName): bool
    {
        return isset($this->config[strtolower($gatewayName)]);
    }
    
    /**
     * Obtém todas as configurações
     * 
     * @return array Todas as configurações
     */
    public function getAllConfigs(): array
    {
        return $this->config;
    }
}