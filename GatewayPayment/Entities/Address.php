<?php

namespace Casdorio\GatewayPayment\Entities;

use CodeIgniter\Entity\Entity;

class Address extends Entity
{
    public function __construct(
        public ?string $address = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $zip_code = null,
        public ?string $country = null,
    ){}
}