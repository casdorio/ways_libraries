<?php

namespace GatewayPayment\Entities;

use GatewayPayment\Exceptions\ValidationException;

class Payment
{
    private float $amount;
    private string $currency;
    private ?CardInfo $cardInfo = null;
    private ?Customer $customer = null;
    private ?string $customerId = null;
    private string $gateway;
    private ?string $description = null;
    private ?string $transactionType = null;
    private array $items = [];

    /**
     * @throws ValidationException
     */
    public function __construct(float $amount, string $currency, string $gateway)
    {
        if ($amount <= 0) {
            throw new ValidationException('Payment amount must be greater than zero');
        }
        if (empty($currency) || strlen($currency) !== 3) {
            throw new ValidationException('Currency must be a valid 3-letter ISO code');
        }
        if (empty($gateway)) {
            throw new ValidationException('Gateway must be specified');
        }

        $this->amount = $amount;
        $this->currency = strtoupper($currency);
        $this->gateway = $gateway;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getGateway(): string
    {
        return $this->gateway;
    }

    public function getCardInfo(): ?CardInfo
    {
        return $this->cardInfo;
    }

    public function setCardInfo(CardInfo $cardInfo): self
    {
        $this->cardInfo = $cardInfo;
        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(Customer $customer): self
    {
        $this->customer = $customer;
        return $this;
    }

    public function getCustomerId(): ?string
    {
        return $this->customerId;
    }

    public function setCustomerId(string $customerId): self
    {
        $this->customerId = $customerId;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getTransactionType(): ?string
    {
        return $this->transactionType;
    }

    public function setTransactionType(string $transactionType): self
    {
        $this->transactionType = $transactionType;
        return $this;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function addItem(Item $item): self
    {
        $this->items[] = $item;
        return $this;
    }
}