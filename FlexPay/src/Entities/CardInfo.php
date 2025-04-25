<?php

namespace GatewayPayment\Entities;

use GatewayPayment\Exceptions\ValidationException;

class CardInfo
{
    private ?string $cardNumber = null;
    private ?int $expirationMonth = null;
    private ?int $expirationYear = null;
    private ?string $cvv = null;
    private ?string $token = null;

    /**
     * @throws ValidationException
     */
    public function __construct(
        ?string $cardNumber = null,
        ?int $expirationMonth = null,
        ?int $expirationYear = null,
        ?string $cvv = null,
        ?string $token = null
    ) {
        if ($cardNumber !== null && !preg_match('/^\d{12,19}$/', $cardNumber)) {
            throw new ValidationException('Invalid card number');
        }
        if ($expirationMonth !== null && ($expirationMonth < 1 || $expirationMonth > 12)) {
            throw new ValidationException('Invalid expiration month');
        }
        if ($expirationYear !== null && ($expirationYear < date('Y') || $expirationYear > date('Y') + 10)) {
            throw new ValidationException('Invalid expiration year');
        }
        if ($cvv !== null && !preg_match('/^\d{3,4}$/', $cvv)) {
            throw new ValidationException('Invalid CVV');
        }
        if ($cardNumber === null && $token === null) {
            throw new ValidationException('Either card number or token must be provided');
        }

        $this->cardNumber = $cardNumber;
        $this->expirationMonth = $expirationMonth;
        $this->expirationYear = $expirationYear;
        $this->cvv = $cvv;
        $this->token = $token;
    }

    public function getCardNumber(): ?string
    {
        return $this->cardNumber;
    }

    public function getExpirationMonth(): ?int
    {
        return $this->expirationMonth;
    }

    public function getExpirationYear(): ?int
    {
        return $this->expirationYear;
    }

    public function getCvv(): ?string
    {
        return $this->cvv;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }
}