<?php

namespace GatewayPayment\Entities;

use GatewayPayment\Exceptions\ValidationException;

class BankAccount
{
    private string $accountNumber;
    private string $routingNumber;
    private string $accountType;
    private string $bankName;
    private string $accountHolderName;

    /**
     * @throws ValidationException
     */
    public function __construct(
        string $accountNumber,
        string $routingNumber,
        string $accountType,
        string $bankName,
        string $accountHolderName
    ) {
        if (!preg_match('/^\d{5,17}$/', $accountNumber)) {
            throw new ValidationException('Invalid bank account number');
        }
        if (!preg_match('/^\d{9}$/', $routingNumber)) {
            throw new ValidationException('Invalid routing number');
        }
        if (!in_array(strtolower($accountType), ['checking', 'savings'], true)) {
            throw new ValidationException('Account type must be checking or savings');
        }
        if (empty($bankName)) {
            throw new ValidationException('Bank name must not be empty');
        }
        if (empty($accountHolderName)) {
            throw new ValidationException('Account holder name must not be empty');
        }

        $this->accountNumber = $accountNumber;
        $this->routingNumber = $routingNumber;
        $this->accountType = strtolower($accountType);
        $this->bankName = $bankName;
        $this->accountHolderName = $accountHolderName;
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function getRoutingNumber(): string
    {
        return $this->routingNumber;
    }

    public function getAccountType(): string
    {
        return $this->accountType;
    }

    public function getBankName(): string
    {
        return $this->bankName;
    }

    public function getAccountHolderName(): string
    {
        return $this->accountHolderName;
    }
}