<?php

namespace Casdorio\GatewayPayment\Entities;

class PaymentResponse
{
    private bool $success;
    private ?string $transactionId;
    private ?string $errorMessage;
    private $rawResponse;
    private ?array $additionalData;
    
    public function __construct(
        bool $success, 
        ?string $transactionId = null, 
        ?string $errorMessage = null, 
        $rawResponse = null,
        ?array $additionalData = []
    ) {
        $this->success = $success;
        $this->transactionId = $transactionId;
        $this->errorMessage = $errorMessage;
        $this->rawResponse = $rawResponse;
        $this->additionalData = $additionalData;
    }
    
    public function isSuccess(): bool
    {
        return $this->success;
    }
    
    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }
    
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }
    
    public function getRawResponse()
    {
        return $this->rawResponse;
    }
    
    public function getAdditionalData(?string $key = null)
    {
        if ($key !== null) {
            return $this->additionalData[$key] ?? null;
        }
        
        return $this->additionalData;
    }
    
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'transaction_id' => $this->transactionId,
            'error_message' => $this->errorMessage,
            'additional_data' => $this->additionalData,
        ];
    }
}