<?php

namespace App\Codec;

use App\Document\Station;
use MongoDB\BSON\Document;
use MongoDB\Codec\DecodeIfSupported;
use MongoDB\Codec\DocumentCodec;
use MongoDB\Codec\EncodeIfSupported;
use MongoDB\Exception\UnsupportedValueException;

/** @template-implements DocumentCodec<Station> */
class StationCodec implements DocumentCodec
{
    use DecodeIfSupported;
    use EncodeIfSupported;

    public function __construct(
        private readonly BinaryUuidCodec $binaryUuidCodec,
        private readonly AddressCodec $addressCodec,
        private readonly PointCodec $pointCodec
    ) {}

    public function canDecode($value): bool
    {
        return $value instanceof Document;
    }

    public function canEncode($value): bool
    {
        return $value instanceof Station;
    }

    public function decode($value): Station
    {
        if (! $this->canDecode($value)) {
            throw UnsupportedValueException::invalidDecodableValue($value);
        }

        $station = new Station($this->binaryUuidCodec->decode($value->_id));

        $station->name = $value->name;
        $station->brand = $value->brand;
        $station->address = $this->addressCodec->decode($value->address);
        $station->location = $this->pointCodec->decode($value->location);

        return $station;
    }

    public function encode($value): Document
    {
        if (! $this->canEncode($value)) {
            throw UnsupportedValueException::invalidEncodableValue($value);
        }

        return Document::fromPHP([
            '_id' => $this->binaryUuidCodec->encode($value->id),
            'name' => $value->name,
            'brand' => $value->brand,
            'address' => $this->addressCodec->encode($value->address),
            'location' => $this->pointCodec->encode($value->location),
        ]);
    }
}
