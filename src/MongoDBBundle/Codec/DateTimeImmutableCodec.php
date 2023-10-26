<?php

namespace MongoDB\Bundle\Codec;

use DateTimeImmutable;
use DateTimeInterface;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Codec\Codec;
use MongoDB\Codec\DecodeIfSupported;
use MongoDB\Codec\EncodeIfSupported;
use MongoDB\Exception\UnsupportedValueException;

/** @template-implements Codec<UTCDateTime, DateTimeImmutable> */
final class DateTimeImmutableCodec implements Codec
{
    use DecodeIfSupported;
    use EncodeIfSupported;

    public function canDecode($value): bool
    {
        return $value instanceof UTCDateTime;
    }

    public function canEncode($value): bool
    {
        return $value instanceof DateTimeInterface;
    }

    /** @param UTCDateTime $value */
    public function decode($value): DateTimeImmutable
    {
        if (! $this->canDecode($value)) {
            throw UnsupportedValueException::invalidDecodableValue($value);
        }

        return DateTimeImmutable::createFromMutable($value->toDateTime());
    }

    public function encode($value): UTCDateTime
    {
        if (! $this->canEncode($value)) {
            throw UnsupportedValueException::invalidEncodableValue($value);
        }

        return new UTCDateTime($value);
    }
}
