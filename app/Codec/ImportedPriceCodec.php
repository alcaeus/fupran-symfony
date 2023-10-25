<?php

namespace App\Codec;

use App\Document\ImportedPrice;
use MongoDB\Bundle\Codec\DateTimeImmutableCodec;
use MongoDB\Bundle\Codec\PropertyMappedDocumentCodec;

final class ImportedPriceCodec extends PropertyMappedDocumentCodec
{
    public function __construct()
    {
        parent::__construct(
            ImportedPrice::class,
            _id: null,
            reportDate: new DateTimeImmutableCodec(),
            station: null,
            fuelType: null,
            price: null,
        );
    }
}
