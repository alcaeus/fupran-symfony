<?php

namespace MongoDB\Bundle\Metadata;

use LogicException;
use ReflectionClass;
use function array_filter;
use function array_flip;
use function array_map;
use function array_merge;
use function array_values;
use function assert;
use function sprintf;

final class Document
{
    private readonly ReflectionClass $reflectionClass;

    /** @var Field[] */
    public readonly array $fields;

    /** @var Field[] */
    public readonly array $persistedFields;

    /** @var string[] */
    public readonly array $persistedFieldNames;

    public function __construct(
        public readonly string $className,
        public readonly ?Field $id = null,
        Field ...$fields,
    ) {
        $this->reflectionClass = new ReflectionClass($this->className);

        if ($this->id !== null) {
            assert($this->id->name === '_id');
        }

        $this->fields = array_values($fields);

        $this->persistedFields = array_filter(
            array_merge([$this->id], $this->fields),
        );

        $this->persistedFieldNames = array_flip(
            array_map(
                fn (Field $field) => $field->name,
                $this->persistedFields,
            ),
        );
    }

    public function createNewInstance(): object
    {
        return $this->reflectionClass->newInstanceWithoutConstructor();
    }

    public function getField(string $fieldName): Field
    {
        if (!$this->hasField($fieldName)) {
            throw new LogicException(sprintf(
                'No field named "%s" was mapped in document class "%s".',
                $fieldName,
                $this->reflectionClass->name,
            ));
        }

        return $this->persistedFields[$this->persistedFieldNames[$fieldName]];
    }

    public function hasField(string $fieldName): bool
    {
        return isset($this->persistedFieldNames[$fieldName]);
    }
}
