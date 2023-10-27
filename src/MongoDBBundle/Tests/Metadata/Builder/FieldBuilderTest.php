<?php

namespace MongoDB\Bundle\Tests\Metadata\Builder;

use MongoDB\Bundle\Attribute\Field as FieldAttribute;
use MongoDB\Bundle\Codec\DateTimeImmutableCodec;
use MongoDB\Bundle\Metadata\Builder\FieldBuilder;
use MongoDB\Bundle\Metadata\Field;
use MongoDB\Bundle\Tests\Fixtures\SimpleTestDocument;
use MongoDB\Bundle\ValueAccessor\ValueGetter;
use MongoDB\Bundle\ValueAccessor\ValueSetter;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;

/** @covers \MongoDB\Bundle\Metadata\Builder\FieldBuilder */
final class FieldBuilderTest extends TestCase
{
    public function testFromAttributeForMethod(): void
    {
        $reflectionMethod = new ReflectionMethod(SimpleTestDocument::class, 'getSquare');

        $builder = FieldBuilder::fromAttribute($reflectionMethod, new FieldAttribute(name: 'square'));

        $field = $builder->build();

        $this->assertInstanceOf(Field::class, $field);
        $this->assertSame('square', $field->name);
        $this->assertNull($field->codec);
        $this->assertNotNull($field->getter);
        $this->assertNull($field->setter);
    }

    public function testFromReflectionMethod(): void
    {
        $document = new SimpleTestDocument(5);
        $reflectionMethod = new ReflectionMethod($document, 'getSquare');

        $builder = FieldBuilder::fromReflectionMethod($reflectionMethod);

        $field = $builder->build();

        $this->assertInstanceOf(Field::class, $field);
        $this->assertSame('getSquare', $field->name);
        $this->assertNull($field->codec);
        $this->assertNotNull($field->getter);
        $this->assertNull($field->setter);

        $document->value = 6;
        $this->assertSame(36, $field->getPHPValue($document));
    }

    public function testFromAttributeForProperty(): void
    {
        $reflectionProperty = new ReflectionProperty(SimpleTestDocument::class, 'value');

        $builder = FieldBuilder::fromAttribute($reflectionProperty, new FieldAttribute());

        $field = $builder->build();

        $this->assertInstanceOf(Field::class, $field);
        $this->assertSame('value', $field->name);
        $this->assertNull($field->codec);
        $this->assertNotNull($field->getter);
        $this->assertNotNull($field->setter);
    }

    public function testFromAttributeWithNameAndCodec(): void
    {
        $reflectionProperty = new ReflectionProperty(SimpleTestDocument::class, 'value');

        $codec = new DateTimeImmutableCodec();
        $builder = FieldBuilder::fromAttribute($reflectionProperty, new FieldAttribute(name: 'baz', codec: $codec));

        $field = $builder->build();

        $this->assertInstanceOf(Field::class, $field);
        $this->assertSame('baz', $field->name);
        $this->assertSame($codec, $field->codec);
        $this->assertNotNull($field->getter);
        $this->assertNotNull($field->setter);
    }

    public function testFromReflectionProperty(): void
    {
        $field = self::createTestBuilder()->build();

        $this->assertInstanceOf(Field::class, $field);
        $this->assertSame('value', $field->name);
        $this->assertNull($field->codec);
        $this->assertNotNull($field->getter);
        $this->assertNotNull($field->setter);

        $document = new SimpleTestDocument(5);
        $field->setPHPValue($document, 6);
        $this->assertSame(6, $field->getPHPValue($document));
        $this->assertSame(36, $document->getSquare());
    }

    public function testWithGetter()
    {
        $getter = new class implements ValueGetter {
            public function __invoke(object $document): mixed
            {
                return 42;
            }
        };

        $field = self::createTestBuilder()
            ->withGetter($getter)
            ->build()
        ;

        $this->assertSame($getter, $field->getter);
    }

    public function testWithName()
    {
        $name = uniqid();

        $field = self::createTestBuilder()
            ->withName($name)
            ->build()
        ;

        $this->assertSame($name, $field->name);
    }

    public function testWithSetter()
    {
        $setter = new class implements ValueSetter {
            public function __invoke(object $document, mixed $value): void
            {
                $document->value = 42;
            }
        };

        $field = self::createTestBuilder()
            ->withSetter($setter)
            ->build()
        ;

        $this->assertSame($setter, $field->setter);
    }

    public function testWithCodec()
    {
        $codec = new DateTimeImmutableCodec();

        $field = self::createTestBuilder()
            ->withCodec($codec)
            ->build()
        ;

        $this->assertSame($codec, $field->codec);
    }

    private static function createTestBuilder(): FieldBuilder
    {
        $document = new SimpleTestDocument(5);
        $reflectionProperty = new ReflectionProperty($document, 'value');

        return FieldBuilder::fromReflectionProperty($reflectionProperty);
    }
}
