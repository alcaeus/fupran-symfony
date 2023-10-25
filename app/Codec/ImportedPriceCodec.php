<?php

namespace App\Codec;

use App\Document\ImportedPrice;
use MongoDB\Bundle\Codec\MappedDocumentCodec;
use MongoDB\Bundle\Metadata\Document;

final class ImportedPriceCodec extends MappedDocumentCodec
{
    public function __construct()
    {
        $metadata = Document::fromAttributes(ImportedPrice::class);

        parent::__construct($metadata);
    }
}
