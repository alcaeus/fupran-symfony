<?php

namespace MongoDB\Bundle\Metadata;

use ReflectionClass;

final class Document
{
    private readonly ReflectionClass $reflection;

    /** @var array<string,Field> */
    public readonly array $fieldMappings;

    public function __construct(
        public readonly string $className,
        Field ...$fieldMappings,
    ) {
        $this->reflection = new ReflectionClass($this->className);
        $this->fieldMappings = $fieldMappings;
    }

    public function getNewInstance(): object
    {
        return $this->reflection->newInstanceWithoutConstructor();
    }
}
