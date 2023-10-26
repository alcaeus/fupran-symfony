<?php

namespace MongoDB\Bundle\Tests\Fixtures;

use MongoDB\BSON\ObjectId;
use MongoDB\Bundle\Metadata\Builder\DocumentBuilder;
use MongoDB\Bundle\Metadata\Document;
use ReflectionClass;

final class SimpleTestDocument
{
    public readonly ObjectId $id;

    public function __construct(
        public int $value,
    ) {
        $this->id = new ObjectId();
    }

    public function getSquare(): int
    {
        return $this->value ** 2;
    }

    public static function getMetadata(): Document
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
