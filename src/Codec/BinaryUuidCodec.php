<?php

namespace App\Codec;

use MongoDB\BSON\Binary;
use MongoDB\Codec\Codec;
use MongoDB\Codec\DecodeIfSupported;
use MongoDB\Codec\EncodeIfSupported;
use MongoDB\Exception\UnsupportedValueException;
use Symfony\Component\Uid\UuidV4;

/** @template-implements Codec<Binary, UuidV4> */
class BinaryUuidCodec implements Codec
{
    use DecodeIfSupported;
    use EncodeIfSupported;

    public function canDecode($value): bool
    {
        return $value instanceof Binary && $value->getType() === Binary::TYPE_UUID;
    }

    public function canEncode($value): bool
    {
        return $value instanceof UuidV4;
    }

    public function decode($value): UuidV4
    {
        return UuidV4::fromString($value->getData());
    }

    public function encode($value): Binary
    {
        if (! $this->canEncode($value)) {
            throw UnsupportedValueException::invalidEncodableValue($value);
        }

        return new Binary($value->toBinary(), Binary::TYPE_UUID);
    }
}
