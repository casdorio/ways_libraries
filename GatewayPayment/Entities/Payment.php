<?php

namespace Casdorio\GatewayPayment\Entities;

use CodeIgniter\Entity\Entity;

class Payment extends Entity
{
    public function __construct(
        public ?string $card_number,
        public ?string $expiration_date,
        public ?string $cvv,
        public string $amount,
        public ?string $invoice_number,
        public ?string $description,
        public ?string $first_name,
        public ?string $last_name,
        public ?string $address,
        public ?string $city,
        // public string $state,
        public ?string $zip_code,
        // public string $country,
        public ?string $customer_id,
        public ?string $email,
    ) {}
}