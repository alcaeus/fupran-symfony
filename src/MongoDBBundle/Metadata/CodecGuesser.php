<?php

namespace MongoDB\Bundle\Metadata;

use MongoDB\Bundle\Codec\ArrayCodec;
use MongoDB\Bundle\Codec\EnumCodec;
use MongoDB\Bundle\Codec\ObjectCodec;
use MongoDB\Codec\Codec;
use ReflectionEnum;
use ReflectionException;
use ReflectionNamedType;
use ReflectionType;
use stdClass;

use function class_exists;

class CodecGuesser
{
    public function guessCodec(ReflectionType $type): ?Codec
    {
        if ($type instanceof ReflectionNamedType) {
            return match ((string) $type) {
                'array' => new ArrayCodec(),
                stdClass::class => new ObjectCodec(),
                default => $this->guessObjectCodec($type),
            };
        }

        return null;
    }

    private function guessObjectCodec(ReflectionNamedType $type): ?Codec
    {
        $className = $type->getName();

        if (! class_exists($className)) {
            return null;
        }

        if ($this->isEnum($className)) {
            return new EnumCodec($className);
        }

        return null;
    }

    private static function isEnum(string $typeName): bool
    {
        try {
            new ReflectionEnum($typeName);
            return true;
        } catch (ReflectionException) {
            return false;
        }
    }
}
