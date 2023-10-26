<?php

namespace MongoDB\Bundle\ValueAccessor;

use ReflectionProperty;

final class ReflectionAccessor
{
    public static function createGetter(ReflectionProperty $property): ValueGetter
    {
        return new class ($property) implements ValueGetter {
            public function __construct(private ReflectionProperty $property) {}

            public function __invoke(object $document): mixed
            {
                return $this->property->isInitialized($document)
                    ? $this->property->getValue($document)
                    : null
                ;
            }
        };
    }

    public static function createSetter(ReflectionProperty $property): ValueSetter
    {
        return new class ($property) implements ValueSetter {
            public function __construct(private ReflectionProperty $property) {}

            public function __invoke(object $document, mixed $value): void
            {
                $this->property->setValue($document, $value);
            }
        };
    }
}
