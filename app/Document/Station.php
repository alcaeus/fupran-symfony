<?php

namespace App\Document;

use App\Codec\PointCodec;
use GeoJson\Geometry\Point;
use MongoDB\BSON\Binary;
use MongoDB\Bundle\Attribute\Document;
use MongoDB\Bundle\Attribute\Field;

#[Document]
class Station
{
    public function __construct(
        #[Field('_id')]
        public readonly Binary $id,

        #[Field]
        public readonly string $name,

        #[Field]
        public readonly string $brand,

        #[Field]
        public readonly Address $address,

        #[Field(codec: new PointCodec())]
        public readonly Point $location,
    ) {
    }
}
