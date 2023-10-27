<?php

namespace MongoDB\Bundle\Metadata;

use MongoDB\Bundle\Codec\ArrayCodec;
use MongoDB\Bundle\Codec\ObjectCodec;
use MongoDB\Codec\Codec;
use ReflectionNamedType;
use ReflectionType;

use stdClass;

class CodecGuesser
{
    public function guessCodec(ReflectionType $type): ?Codec
    {
        if ($type instanceof ReflectionNamedType) {
            return match ((string) $type) {
                'array' => new ArrayCodec(),
                stdClass::class => new ObjectCodec(),
                default => null,
            };
        }

        return null;
    }
}
