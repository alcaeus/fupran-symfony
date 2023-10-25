<?php

namespace App\Document;

use DateTimeImmutable;
use MongoDB\BSON\Binary;
use MongoDB\BSON\ObjectId;
use MongoDB\Bundle\Attribute\Document;
use MongoDB\Bundle\Attribute\Field;
use MongoDB\Bundle\Codec\DateTimeImmutableCodec;

#[Document]
class ImportedPrice
{
    #[Field('_id')]
    public readonly ObjectId $id;

    public function __construct(
        #[Field(codec: new DateTimeImmutableCodec())]
        public readonly DateTimeImmutable $reportDate,
        #[Field]
        public readonly Binary $station,
        #[Field]
        public readonly string $fuelType,
        #[Field]
        public readonly float $price,
    ) {
        $this->id = new ObjectId();
    }
}
