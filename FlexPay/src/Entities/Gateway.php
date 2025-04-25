<?php

namespace GatewayPayment\Entities;

use GatewayPayment\Exceptions\ValidationException;

class Gateway
{
    private string $name;
    private array $supportedTransactionTypes;
    private array $supportedCurrencies;

    /**
     * @throws ValidationException
     */
    public function __construct(string $name, array $supportedTransactionTypes = [], array $supportedCurrencies = [])
    {
        if (empty($name)) {
            throw new ValidationException('Gateway name must not be empty');
        }

        $this->name = $name;
        $this->supportedTransactionTypes = $supportedTransactionTypes;
        $this->supportedCurrencies = $supportedCurrencies;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSupportedTransactionTypes(): array
    {
        return $this->supportedTransactionTypes;
    }

    public function getSupportedCurrencies(): array
    {
        return $this->supportedCurrencies;
    }

    public function supportsTransactionType(string $transactionType): bool
    {
        return in_array($transactionType, $this->supportedTransactionTypes, true);
    }

    public function supportsCurrency(string $currency): bool
    {
        return in_array(strtoupper($currency), $this->supportedCurrencies, true);
    }
}