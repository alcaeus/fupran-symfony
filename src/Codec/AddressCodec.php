<?php

namespace App\Codec;

use App\Document\Address;
use MongoDB\BSON\Document;
use MongoDB\Codec\DecodeIfSupported;
use MongoDB\Codec\DocumentCodec;
use MongoDB\Codec\EncodeIfSupported;
use MongoDB\Exception\UnsupportedValueException;

/** @template-implements DocumentCodec<Address> */
class AddressCodec implements DocumentCodec
{
    use DecodeIfSupported;
    use EncodeIfSupported;

    public function canDecode($value): bool
    {
        return $value instanceof Document;
    }

    public function canEncode($value): bool
    {
        return $value instanceof Address;
    }

    public function decode($value): Address
    {
        if (! $this->canDecode($value)) {
            throw UnsupportedValueException::invalidDecodableValue($value);
        }

        return new Address(
            $value->street,
            $value->houseNumber,
            $value->postCode,
            $value->city,
        );
    }

    public function encode($value): Document
    {
        if (! $this->canEncode($value)) {
            throw UnsupportedValueException::invalidEncodableValue($value);
        }

        return Document::fromPHP($value);
    }
}
