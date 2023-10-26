<?php

namespace MongoDB\Bundle\Tests\Metadata\Builder;

use MongoDB\Bundle\Codec\DateTimeImmutableCodec;
use MongoDB\Bundle\Metadata\Builder\DocumentBuilder;
use MongoDB\Bundle\Metadata\Builder\FieldBuilder;
use MongoDB\Bundle\Metadata\Field;
use MongoDB\Bundle\Tests\Fixtures\SimpleTestDocument;
use MongoDB\Bundle\ValueAccessor\ValueGetter;
use Generator;
use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use stdClass;

/** @covers \MongoDB\Bundle\Metadata\Builder\DocumentBuilder */
final class DocumentBuilderTest extends TestCase
{
    public function testFromReflectionClass(): void
    {
        $metadata = self::createDocumentBuilder()->build();

        $this->assertSame(SimpleTestDocument::class, $metadata->className);
        $this->assertCount(2, $metadata->fields);

        $this->assertNotNull($metadata->id);
        $this->assertSame('value', $metadata->fields[0]->name);
        $this->assertSame('square', $metadata->fields[1]->name);
    }

    public function testFromReflectionClassWithoutIdentifier(): void
    {
        $document = self::createDocumentBuilder(null)->build();

        $this->assertCount(2, $document->fields);

        $this->assertNull($document->id);
        $this->assertSame('value', $document->fields[0]->name);
        $this->assertSame('square', $document->fields[1]->name);
    }

    public function testFromReflectionClassWithOverriddenFieldName(): void
    {
        $document = DocumentBuilder
            ::fromReflectionClass(
                new ReflectionClass(SimpleTestDocument::class),
                null,
                otherValue: 'value',
            )
            ->build()
        ;

        $this->assertCount(1, $document->fields);

        $this->assertNull($document->id);
        $this->assertSame('otherValue', $document->fields[0]->name);
    }

    /** @dataProvider dataTestFromReflectionClassWithInvalidField */
    public function testFromReflectionClassWithInvalidField(
        string $expectedException,
        string $field
    ): void {
        $reflectionClass = new ReflectionClass(SimpleTestDocument::class);

        $this->expectException($expectedException);
        DocumentBuilder::fromReflectionClass(
            $reflectionClass,
            null,
            $field,
        );
    }

    public static function dataTestFromReflectionClassWithInvalidField(): Generator
    {
        yield 'Empty string' => [
            'expectedException' => LogicException::class,
            'field' => '',
        ];

        yield 'Empty method name' => [
            'expectedException' => LogicException::class,
            'field' => '()',
        ];

        yield 'Method does not exist' => [
            'expectedException' => ReflectionException::class,
            'field' => 'foo()',
        ];

        yield 'Property does not exist' => [
            'expectedException' => LogicException::class,
            'field' => 'foo',
        ];
    }

    public function testClosureAsField(): void
    {
        $codec = new DateTimeImmutableCodec();
        $closure = fn (FieldBuilder $builder) => $builder->withCodec($codec);

        $metadata = DocumentBuilder
            ::fromReflectionClass(
                new ReflectionClass(SimpleTestDocument::class),
                null,
                value: $closure,
            )
            ->build()
        ;

        $this->assertSame($codec, $metadata->fields[0]->codec);
    }

    public function testClosureAsFieldDoesNotAllowChangingName(): void
    {
        $closure = fn (FieldBuilder $builder) => $builder->withName('bar');

        $metadata = DocumentBuilder
            ::fromReflectionClass(
                new ReflectionClass(SimpleTestDocument::class),
                null,
                value: $closure,
            )
            ->build()
        ;

        $this->assertSame('value', $metadata->fields[0]->name);
    }

    public function testClosureAsFieldDoesNotWorkForUnnamedArguments(): void
    {
        $closure = fn (FieldBuilder $builder) => $builder->withName('bar');

        $this->expectException(LogicException::class);
        $metadata = DocumentBuilder
            ::fromReflectionClass(
                new ReflectionClass(SimpleTestDocument::class),
                null,
                $closure,
            )
            ->build()
        ;
    }

    public function testWithClass(): void
    {
        $document = self::createDocumentBuilder()
            ->withClass(stdClass::class)
            ->build()
        ;

        $this->assertSame(stdClass::class, $document->className);
    }

    public function testWithId(): void
    {
        $valueField = FieldBuilder
            ::fromReflectionProperty(new ReflectionProperty(SimpleTestDocument::class, 'value'))
            ->build()
        ;

        $idField = (new FieldBuilder($valueField))
            ->withName('_id')
            ->build()
        ;

        $document = self::createDocumentBuilder()
            ->withId($valueField)
            ->build()
        ;

        $this->assertEquals($idField, $document->id);
        $this->assertCount(2, $document->fields);
    }

    public function testWithNoId(): void
    {
        $document = self::createDocumentBuilder()
            ->withId(null)
            ->build()
        ;

        $this->assertNull($document->id);
        $this->assertCount(2, $document->fields);
    }

    public function testWithFields(): void
    {
        $document = self::createDocumentBuilder()
            ->withFields(
                new Field(
                    'foo',
                    null,
                    new class implements ValueGetter {
                        public function __invoke(object $document): mixed
                        {
                            return 42;
                        }
                    },
                    null
                )
            )
            ->build()
        ;

        $this->assertNotNull($document->id);
        $this->assertCount(1, $document->fields);
        $this->assertSame('foo', $document->fields[0]->name);
    }

    private static function createDocumentBuilder(?string $id = 'id'): DocumentBuilder
    {
        return DocumentBuilder::fromReflectionClass(
            new ReflectionClass(SimpleTestDocument::class),
            $id,
            'value',
            square: 'getSquare()',
        );
    }
}
