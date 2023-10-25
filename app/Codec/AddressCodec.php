<?php

namespace App\Codec;

use App\Document\Address;
use MongoDB\Bundle\Codec\MappedDocumentCodec;
use MongoDB\Bundle\Metadata\Document;

final class AddressCodec extends MappedDocumentCodec
{
    public function __construct()
    {
        $metadata = Document::fromAttributes(Address::class);

        parent::__construct($metadata);
    }
}
