<?php

namespace App\Codec;

use GeoJson\Geometry\Point;
use MongoDB\BSON\Document;
use MongoDB\BSON\PackedArray;
use MongoDB\Codec\Codec;
use MongoDB\Codec\DecodeIfSupported;
use MongoDB\Codec\EncodeIfSupported;
use MongoDB\Exception\UnsupportedValueException;

/** @template-implements Codec<Document, Point> */
class PointCodec implements Codec
{
    use DecodeIfSupported;
    use EncodeIfSupported;

    public function canDecode($value): bool
    {
        return $value instanceof Document
            && isset($value->type)
            && $value->type === 'Point'
            && isset($value->coordinates)
            && $value->coordinates instanceof PackedArray;
    }

    public function canEncode($value): bool
    {
        return $value instanceof Point;
    }

    public function decode($value): Point
    {
        if (! $this->canDecode($value)) {
            throw UnsupportedValueException::invalidDecodableValue($value);
        }

        return new Point($value->coordinates->toPHP());
    }

    public function encode($value)
    {
        if (! $this->canEncode($value)) {
            throw UnsupportedValueException::invalidEncodableValue($value);
        }

        return Document::fromPHP([
            'type' => 'Point',
            'coordinates' => $value->getCoordinates(),
        ]);
    }
}
