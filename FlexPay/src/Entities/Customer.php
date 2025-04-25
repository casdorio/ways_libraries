<?php

namespace GatewayPayment\Entities;

use GatewayPayment\Exceptions\ValidationException;

class Customer
{
    private string $name;
    private string $email;
    private ?CardInfo $cardInfo = null;
    private ?Address $billingAddress = null;
    private ?Address $shippingAddress = null;
    private ?string $customerId = null;

    /**
     * @throws ValidationException
     */
    public function __construct(string $name, string $email)
    {
        if (empty($name)) {
            throw new ValidationException('Customer name must not be empty');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Invalid email address');
        }

        $this->name = $name;
        $this->email = $email;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
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

    public function getBillingAddress(): ?Address
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(Address $billingAddress): self
    {
        $this->billingAddress = $billingAddress;
        return $this;
    }

    public function getShippingAddress(): ?Address
    {
        return $this->shippingAddress;
    }

    public function setShippingAddress(Address $shippingAddress): self
    {
        $this->shippingAddress = $shippingAddress;
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
}