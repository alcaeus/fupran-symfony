<?php

namespace App\Codec;

use App\Document\Location;
use MongoDB\Bundle\Codec\MappedDocumentCodec;
use MongoDB\Bundle\Metadata\Document;

final class LocationCodec extends MappedDocumentCodec
{
    public function __construct()
    {
        $metadata = Document::fromAttributes(Location::class);

        parent::__construct($metadata);
    }
}
