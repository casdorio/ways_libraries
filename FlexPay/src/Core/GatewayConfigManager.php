<?php

namespace GatewayPayment\Core;

use CodeIgniter\Config\Services;
use GatewayPayment\Exceptions\ConfigurationException;
use GatewayPayment\Config\GatewayConfig;

class GatewayConfigManager
{
    private GatewayConfig $config;

    public function __construct()
    {
        $this->config = Services::config(GatewayConfig::class);
    }

    public function getConfig(string $gateway): array
    {
        if (!isset($this->config->gateways[$gateway])) {
            throw new ConfigurationException("Configuration for gateway {$gateway} not found");
        }
        return $this->config->gateways[$gateway];
    }

    public function getDefaultGateway(): string
    {
        return $this->config->defaultGateway;
    }

    public function getDefaultCurrency(): string
    {
        return $this->config->defaultCurrency;
    }
}