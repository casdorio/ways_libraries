<?php

namespace GatewayPayment\Entities;

class PaymentResponse
{
    private string $transactionId;
    private string $status;
    private float $amount;
    private string $currency;
    private ?string $responseData = null;

    public function __construct()
    {
        $this->transactionId = '';
        $this->status = 'pending';
        $this->amount = 0.0;
        $this->currency = '';
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function setTransactionId(string $transactionId): self
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    public function getResponseData(): ?string
    {
        return $this->responseData;
    }

    public function setResponseData(string $responseData): self
    {
        $this->responseData = $responseData;
        return $this;
    }

    public function isSuccessful(): bool
    {
        return in_array(strtolower($this->status), ['success', 'approved', 'completed'], true);
    }
}