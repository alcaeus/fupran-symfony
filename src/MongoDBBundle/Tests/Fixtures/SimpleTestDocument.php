<?php

namespace MongoDB\Bundle\Tests\Fixtures;

use MongoDB\BSON\ObjectId;
use MongoDB\Bundle\Attribute\Document;
use MongoDB\Bundle\Attribute\Field;
use MongoDB\Bundle\Metadata\Builder\DocumentBuilder;
use MongoDB\Bundle\Metadata\Document as DocumentMetadata;
use ReflectionClass;

#[Document]
final class SimpleTestDocument
{
    #[Field(name: '_id')]
    public readonly ObjectId $id;

    public function __construct(
        #[Field]
        public int $value,
    ) {
        $this->id = new ObjectId();
    }

    #[Field(name: 'square')]
    public function getSquare(): int
    {
        return $this->value ** 2;
    }

    public static function getMetadata(): DocumentMetadata
    {
        $reflectionClass = new ReflectionClass(self::class);

        return DocumentBuilder
            ::fromReflectionClass(
                $reflectionClass,
                'id',
                'value',
                square: 'getSquare()',
            )
            ->build()
            ;
    }
}
