<?php

namespace MongoDB\Bundle\Codec;

use MongoDB\BSON\Document;
use MongoDB\Codec\DecodeIfSupported;
use MongoDB\Codec\DocumentCodec;
use MongoDB\Codec\EncodeIfSupported;
use MongoDB\Exception\UnsupportedValueException;

use stdClass;
use function array_map;

/**
 * @template-implements DocumentCodec<stdClass>
 */
class ObjectCodec implements DocumentCodec
{
    use DecodeIfSupported;
    use EncodeIfSupported;

    private const TYPEMAP = ['root' => stdClass::class, 'array' => 'array', 'document' => stdClass::class];

    public function canDecode($value): bool
    {
        return $value instanceof Document;
    }

    public function canEncode($value): bool
    {
        return $value instanceof stdClass;
    }

    public function decode($value): stdClass
    {
        if (! $this->canDecode($value)) {
            throw UnsupportedValueException::invalidDecodableValue($value);
        }

        return $value->toPHP(self::TYPEMAP);
    }

    public function encode($value): Document
    {
        if (! $this->canEncode($value)) {
            throw UnsupportedValueException::invalidEncodableValue($value);
        }

        return Document::fromPHP($value);
    }
}
