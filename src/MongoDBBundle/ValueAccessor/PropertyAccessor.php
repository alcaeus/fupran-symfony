<?php

namespace MongoDB\Bundle\ValueAccessor;

final class PropertyAccessor
{
    public static function createGetter(string $propertyName): ValueGetter
    {
        return new class ($propertyName) implements ValueGetter {
            public function __construct(private string $propertyName) {}

            public function __invoke(object $document): mixed
            {
                return $document->{$this->propertyName};
            }
        };
    }

    public static function createSetter(string $propertyName): ValueSetter
    {
        return new class ($propertyName) implements ValueSetter {
            public function __construct(private string $propertyName) {}

            public function __invoke(object $document, mixed $value): void
            {
                $document->{$this->propertyName} = $value;
            }
        };
    }
}
