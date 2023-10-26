<?php

namespace MongoDB\Bundle\Tests\Metadata;

use MongoDB\Bundle\Metadata\Builder\FieldBuilder;
use MongoDB\Bundle\Metadata\Document;
use MongoDB\Bundle\Tests\Fixtures\SimpleTestDocument;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/** @covers \MongoDB\Bundle\Metadata\Document */
final class DocumentTest extends TestCase
{
    public function testConstructor(): void
    {
        $reflectionClass = new ReflectionClass(SimpleTestDocument::class);

        $idField = FieldBuilder
            ::fromReflectionProperty($reflectionClass->getProperty('id'))
            ->withName('_id')
            ->build();

        $valueField = FieldBuilder::fromReflectionProperty($reflectionClass->getProperty('value'))->build();
        $squareField = FieldBuilder::fromReflectionMethod($reflectionClass->getMethod('getSquare'))
            ->withName('square')
            ->build();

        $document = new Document(
            SimpleTestDocument::class,
            $idField,
            $valueField,
            $squareField,
        );

        $this->assertSame(SimpleTestDocument::class, $document->className);
        $this->assertSame($idField, $document->id);
        $this->assertSame([$valueField, $squareField], $document->fields);
    }
}
