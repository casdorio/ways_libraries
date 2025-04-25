<?php

namespace GatewayPayment\Entities;

use GatewayPayment\Exceptions\ValidationException;

class Address
{
    private string $street;
    private string $city;
    private string $state;
    private string $postalCode;
    private string $country;

    /**
     * @throws ValidationException
     */
    public function __construct(string $street, string $city, string $state, string $postalCode, string $country)
    {
        if (empty($street)) {
            throw new ValidationException('Street address must not be empty');
        }
        if (empty($city)) {
            throw new ValidationException('City must not be empty');
        }
        if (empty($state)) {
            throw new ValidationException('State must not be empty');
        }
        if (empty($postalCode) || !preg_match('/^\d{5}(-\d{4})?$/', $postalCode)) {
            throw new ValidationException('Invalid postal code');
        }
        if (empty($country) || strlen($country) !== 2) {
            throw new ValidationException('Country must be a valid 2-letter ISO code');
        }

        $this->street = $street;
        $this->city = $city;
        $this->state = $state;
        $this->postalCode = $postalCode;
        $this->country = strtoupper($country);
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getCountry(): string
    {
        return $this->country;
    }
}