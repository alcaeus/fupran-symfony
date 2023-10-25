<?php

namespace App\Document;

use MongoDB\Bundle\Attribute\Document;
use MongoDB\Bundle\Attribute\Field;

#[Document]
class Address
{
    public function __construct(
        #[Field]
        public readonly string $street,
        #[Field]
        public readonly string $houseNumber,
        #[Field]
        public readonly string $postCode,
        #[Field]
        public readonly string $city,
    ) {
    }
}
