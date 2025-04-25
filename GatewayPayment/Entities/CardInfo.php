<?php

namespace Casdorio\GatewayPayment\Entities;

use CodeIgniter\Entity\Entity;

class CardInfo extends Entity
{
    public function __construct(
        public ?string $card_number = null,
        public ?string $expiration_date = null,
        public ?string $cvv = null,
    ) {}
}