<?php

namespace MongoDB\Bundle\Codec;

use MongoDB\BSON\PackedArray;
use MongoDB\Codec\Codec;
use MongoDB\Codec\DecodeIfSupported;
use MongoDB\Codec\EncodeIfSupported;
use MongoDB\Exception\UnsupportedValueException;

use function array_is_list;
use function array_map;

class ArrayCodec implements Codec
{
    use DecodeIfSupported;
    use EncodeIfSupported;

    public function __construct(
        private readonly ?Codec $valueCodec = null,
    ) {}

    public function canDecode($value): bool
    {
        return $value instanceof PackedArray;
    }

    public function canEncode($value): bool
    {
        return array_is_list($value);
    }

    public function decode($value)
    {
        if (! $this->canDecode($value)) {
            throw UnsupportedValueException::invalidDecodableValue($value);
        }

        $decodeValue = $this->valueCodec !== null
            ? fn($value) => $this->valueCodec->decode($value)
            : fn($value) => $value;

        $decodedArray = [];

        foreach ($value as $item) {
            $decodedArray[] = $decodeValue($item);
        }

        return $decodedArray;
    }

    public function encode($value)
    {
        if (! $this->canEncode($value)) {
            throw UnsupportedValueException::invalidEncodableValue($value);
        }

        $encodeValue = $this->valueCodec !== null
            ? fn($value) => $this->valueCodec->encode($value)
            : fn($value) => $value;

        return PackedArray::fromPHP(array_map($encodeValue, $value));
    }
}
