<?php

namespace App\Document;

class Address
{
    public function __construct(
        public string $street,
        public string $houseNumber,
        public string $postCode,
        public string $city,
    ) {}
}
