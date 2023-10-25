<?php

namespace App\Codec;

use App\Document\Station;
use MongoDB\Bundle\Codec\MappedDocumentCodec;
use MongoDB\Bundle\Metadata\Document;

final class StationCodec extends MappedDocumentCodec
{
    public function __construct()
    {
        $metadata = Document::fromAttributes(Station::class);

        parent::__construct($metadata);
    }
}
