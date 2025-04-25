<?php

namespace Casdorio\GatewayPayment\Entities;

use CodeIgniter\Entity\Entity;

class Payment extends Entity
{
    public ?Item $items = null;
    public ?Address $delivery_address = null;
    public ?Address $billing_address = null;
    public ?CardInfo $card_info = null;

    public function __construct(
        public string $amount,
        public ?string $invoice_number,
        public ?string $description,
        public ?string $first_name,
        public ?string $last_name,
        public ?string $customer_id,
        public ?string $email,
        public ?string $phoneNumber,
        ?Item $items = null,
        ?Address $delivery_address = null,
        ?Address $billing_address = null,
        ?CardInfo $card_info = null
    ) {
        parent::__construct();

        $this->items = $items;
        $this->delivery_address = $delivery_address;
        $this->billing_address = $billing_address;
        $this->card_info = $card_info;
    }
}