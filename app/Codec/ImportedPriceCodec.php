<?php

namespace App\Codec;

use App\Document\ImportedPrice;
use MongoDB\Bundle\Codec\DateTimeImmutableCodec;
use MongoDB\Bundle\Codec\MappedDocumentCodec;
use MongoDB\Bundle\Metadata\Document;
use MongoDB\Bundle\Metadata\Field;

final class ImportedPriceCodec extends MappedDocumentCodec
{
    public function __construct()
    {
        $metadata = new Document(
            ImportedPrice::class,
            new Field('id', '_id'),
            new Field('reportDate', codec: new DateTimeImmutableCodec()),
            new Field('station'),
            new Field('fuelType'),
            new Field('price'),
        );

        parent::__construct($metadata);
    }
}
