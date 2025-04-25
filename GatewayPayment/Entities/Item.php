<?php

namespace Casdorio\GatewayPayment\Entities;

use CodeIgniter\Entity\Entity;

class Item extends Entity
{
    public function __construct(
        public ?string $itemId = null,
        public ?string $name = null,
        public ?string $quantity = null,
        public ?string $description = null,
        public ?float $unitPrice = null,
    ){}
}