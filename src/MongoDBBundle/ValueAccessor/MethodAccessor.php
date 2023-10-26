<?php

namespace MongoDB\Bundle\ValueAccessor;

use ReflectionMethod;

final class MethodAccessor
{
    public static function createGetter(ReflectionMethod $method, mixed ...$args): ValueGetter
    {
        return new class ($method, ...$args) implements ValueGetter {
            private array $args;

            public function __construct(
                private ReflectionMethod $method,
                mixed ...$args,
            ) {
                $this->args = $args;
            }

            public function __invoke(object $document): mixed
            {
                return $this->method->invoke($document, ...$this->args);
            }
        };
    }
}
