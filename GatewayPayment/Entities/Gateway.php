<?php

namespace Casdorio\GatewayPayment\Entities;

use CodeIgniter\Entity\Entity;

class Gateway extends Entity
{
    public function __construct(
        public string $name,
        public string $login_id,
        public string $transaction_key,
        public bool $sandbox,
        public ?bool $is_default = false
    ) {}
}