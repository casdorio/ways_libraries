<?php

namespace GatewayPayment\Entities;

use GatewayPayment\Exceptions\ValidationException;

class Item
{
    private string $name;
    private float $unitPrice;
    private int $quantity;
    private ?string $description = null;

    /**
     * @throws ValidationException
     */
    public function __construct(string $name, float $unitPrice, int $quantity)
    {
        if (empty($name)) {
            throw new ValidationException('Item name must not be empty');
        }
        if ($unitPrice <= 0) {
            throw new ValidationException('Unit price must be greater than zero');
        }
        if ($quantity <= 0) {
            throw new ValidationException('Quantity must be greater than zero');
        }

        $this->name = $name;
        $this->unitPrice = $unitPrice;
        $this->quantity = $quantity;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUnitPrice(): float
    {
        return $this->unitPrice;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getTotalPrice(): float
    {
        return $this->unitPrice * $this->quantity;
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
}