<?php

namespace App\Codec;

use GeoJson\Geometry\Point;
use MongoDB\BSON\Document;
use MongoDB\BSON\PackedArray;
use MongoDB\Codec\DecodeIfSupported;
use MongoDB\Codec\DocumentCodec;
use MongoDB\Codec\EncodeIfSupported;
use MongoDB\Exception\UnsupportedValueException;

/** @template-implements DocumentCodec<Point> */
class PointCodec implements DocumentCodec
{
    use DecodeIfSupported;
    use EncodeIfSupported;

    public function canDecode($value): bool
    {
        return $value instanceof Document
            && $value->has('type') && $value->get('type') == 'Point';
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

        $coordinates = $value->coordinates;
        assert($coordinates instanceof PackedArray);

        return new Point($coordinates->toPHP());
    }

    public function encode($value): Document
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
