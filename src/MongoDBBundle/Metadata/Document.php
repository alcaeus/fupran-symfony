<?php

namespace MongoDB\Bundle\Metadata;

use Exception;
use MongoDB\Bundle\Attribute\Document as DocumentAttribute;
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

    public static function fromAttributes(string $className): self
    {
        $reflection = new ReflectionClass($className);
        if (! $reflection->getAttributes(DocumentAttribute::class)) {
            throw new Exception(sprintf('Class "%s" is not mapped as a document class.', $className));
        }

        $fieldMappings = [];
        foreach ($reflection->getProperties() as $property) {
            $fieldMappings[] = Field::fromAttributes($property);
        }

        return new self(
            $className,
            ...array_filter($fieldMappings),
        );
    }

    public function getNewInstance(): object
    {
        return $this->reflection->newInstanceWithoutConstructor();
    }
}
