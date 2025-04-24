<?php

namespace Casdorio\GatewayPayment\Entities;

use CodeIgniter\Entity\Entity;

class Item extends Entity
{
    public ?string $name = null;
    public ?string $sku = null;
    public ?float $price = null;
    public ?int $quantity = null;
}