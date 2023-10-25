<?php

namespace App\Document;

use DateTimeImmutable;
use MongoDB\BSON\Binary;
use MongoDB\BSON\ObjectId;

class ImportedPrice
{
    // TODO: Allow renaming fields
    public readonly ObjectId $_id;

    public function __construct(
        public readonly DateTimeImmutable $reportDate,
        public readonly Binary $station,
        public readonly string $fuelType,
        public readonly float $price,
    ) {
        $this->_id = new ObjectId();
    }
}
