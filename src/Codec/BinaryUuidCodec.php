<?php

namespace App\Codec;

use MongoDB\BSON\Binary;
use MongoDB\Codec\Codec;
use MongoDB\Codec\DecodeIfSupported;
use MongoDB\Codec\EncodeIfSupported;
use MongoDB\Exception\UnsupportedValueException;
use Symfony\Component\Uid\Uuid;

use function is_string;
use function preg_match;

/** @template-implements Codec<Binary, Uuid> */
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
        return $value instanceof Uuid || $this->isValidUuidString($value);
    }

    public function decode($value): Uuid
    {
        if (! $this->canDecode($value)) {
            throw UnsupportedValueException::invalidDecodableValue($value);
        }

        return Uuid::fromString($value->getData());
    }

    public function encode($value): Binary
    {
        if (! $this->canEncode($value)) {
            throw UnsupportedValueException::invalidEncodableValue($value);
        }

        if (is_string($value)) {
            $value = new Uuid($value);
        }

        return new Binary($value->toBinary(), Binary::TYPE_UUID);
    }

    private function isValidUuidString(mixed $value): bool
    {
        return is_string($value)
            // Regular expression extracted from Symfony\Component\Uid\Uuid but modified to not check the variant
            && preg_match('{^[0-9a-f]{8}(?:-[0-9a-f]{4}){2}-[0-9a-f]{4}-[0-9a-f]{12}$}Di', $value);
    }
}
