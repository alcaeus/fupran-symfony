<?php

namespace MongoDB\Bundle\Metadata\Builder;

use MongoDB\Bundle\Metadata\Document;
use MongoDB\Bundle\Metadata\Field;
use Closure;
use LogicException;
use ReflectionClass;

use function array_keys;
use function array_map;
use function array_values;
use function is_string;
use function preg_match;
use function sprintf;

final class DocumentBuilder implements MetadataBuilder
{
    private ReflectionClass $reflectionClass;

    public function __construct(private Document $document)
    {
        $this->reflectionClass = new ReflectionClass($this->document->className);
    }

    public static function fromReflectionClass(
        ReflectionClass $reflectionClass,
        string|Field|null $id,
        string|Field|Closure ...$fields,
    ): self {
        if ($id !== null) {
            // Ensure the identifier is always named _id
            $id = self::createField($reflectionClass, $id, '_id');
        }

        return new self(new Document(
            $reflectionClass->name,
            $id,
            ...self::createFields($reflectionClass, $fields),
        ));
    }

    public function withClass(string $class): self
    {
        $this->document = new Document(
            $class,
            $this->document->id,
            ...$this->document->fields,
        );

        $this->reflectionClass = new ReflectionClass($this->document->className);

        return $this;
    }

    public function withId(string|Field|null $id): self
    {
        $this->document = new Document(
            $this->document->className,
            $id !== null
                ? self::createField($this->reflectionClass, $id, '_id')
                : null,
            ...$this->document->fields,
        );

        return $this;
    }

    public function withFields(string|Field ...$fields): self
    {
        $this->document = new Document(
            $this->document->className,
            $this->document->id,
            ...self::createFields($this->reflectionClass, $fields),
        );

        return $this;
    }

    public function build(): Document
    {
        return $this->document;
    }

    /** @param array<string|Field|Closure> $fields */
    private static function createFields(ReflectionClass $reflectionClass, array $fields): array
    {
        return array_map(
            fn (string|Field|Closure $field, int|string $fieldName) => self::createField($reflectionClass, $field, $fieldName),
            array_values($fields),
            array_keys($fields),
        );
    }

    private static function createField(ReflectionClass $reflectionClass, string|Field|Closure $field, int|string $fieldName): Field
    {
        switch (true) {
            case is_string($field):
                $builder = self::createFieldBuilderFromString($reflectionClass, $field);
                break;

            case $field instanceof Field:
                $builder = new FieldBuilder($field);
                break;

            case $field instanceof Closure:
                if (!is_string($fieldName)) {
                    throw new LogicException(sprintf(
                        'Cannot infer field name for closure with key "%d" for class "%s".',
                        $fieldName,
                        $reflectionClass->name,
                    ));
                }

                $builder = self::createFieldBuilderFromString(
                    $reflectionClass,
                    $fieldName,
                );

                $field($builder);
                break;

            default:
                throw new \Exception(sprintf('Invalid field type "%s" encountered.', get_debug_type($field)));
        }

        if (is_string($fieldName)) {
            $builder->withName($fieldName);
        }

        return $builder->build();
    }

    private static function createFieldBuilderFromString(ReflectionClass $reflectionClass, string $name): FieldBuilder
    {
        if (!preg_match('#^(?<name>[^(]+?)(?<isMethod>\(\))?$#', $name, $matches)) {
            throw new LogicException(sprintf(
                'Invalid name "%s" encountered while creating field for class "%s".',
                $name,
                $reflectionClass->name,
            ));
        }

        $memberName = $matches['name'];

        if (isset($matches['isMethod'])) {
            $builder = FieldBuilder::fromReflectionMethod($reflectionClass->getMethod($memberName));
        } elseif ($reflectionClass->hasProperty($memberName)) {
            $builder = FieldBuilder::fromReflectionProperty($reflectionClass->getProperty($memberName));
        } else {
            throw new LogicException(sprintf(
                'Could not find a method or property named "%s" in class "%s".',
                $name,
                $reflectionClass->name,
            ));
        }

        return $builder;
    }
}
