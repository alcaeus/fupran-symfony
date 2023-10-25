<?php

namespace App\Document;

use MongoDB\Bundle\Attribute\Document;
use MongoDB\Bundle\Attribute\Field;
use MongoDB\Bundle\Codec\ArrayCodec;

#[Document]
class Location
{
    #[Field(codec: new ArrayCodec())]
    /** @var array{0: float, 1: float} $coordinates */
    public readonly array $coordinates;

    public function __construct(
        #[Field]
        public readonly string $type,
        public readonly float $longitude,
        public readonly float $latitude,
    ) {
        $this->coordinates = [$this->longitude, $this->latitude];
    }
}
