<?php

namespace MongoDB\Bundle\Tests\Metadata;

use MongoDB\Bundle\Metadata\Builder\FieldBuilder;
use MongoDB\Bundle\Metadata\Field;
use MongoDB\Bundle\ValueAccessor\PropertyAccessor;
use MongoDB\Bundle\ValueAccessor\ValueGetter;
use MongoDB\Bundle\ValueAccessor\ValueSetter;
use Generator;
use LogicException;
use MongoDB\Codec\Codec;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;
use function uniqid;

/** @covers \MongoDB\Bundle\Metadata\Field */
final class FieldTest extends TestCase
{
    /** @dataProvider dataTestConstructor */
    public function testConstructor(
        string $name,
        ?Codec $codec,
        ?ValueGetter $getter,
        ?ValueSetter $setter
    ): void {
        $field = new Field($name, $codec, $getter, $setter);

        $this->assertSame($name, $field->name);
        $this->assertSame($codec, $field->codec);
        $this->assertSame($getter, $field->getter);
        $this->assertSame($setter, $field->setter);

        $document = self::createDocument();

        $document->value = 5;

        if ($getter) {
            $this->assertSame(5, $getter($document));
            $this->assertSame(5, $field->getPHPValue($document));
        }

        if ($setter) {
            $setter($document, 42);
            $this->assertSame(42, $document->value);

            $field->setPHPValue($document, 5);
            $this->assertSame(5, $document->value);
        }
    }

    public static function dataTestConstructor(): Generator
    {
        yield 'Basic field' => [
            'name' => uniqid('field'),
            'codec' => null,
            'getter' => PropertyAccessor::createGetter('value'),
            'setter' => PropertyAccessor::createSetter('value'),
        ];

        yield 'Field without type' => [
            'name' => uniqid('field'),
            'codec' => null,
            'getter' => PropertyAccessor::createGetter('value'),
            'setter' => PropertyAccessor::createSetter('value'),
        ];

        yield 'Field without getter' => [
            'name' => uniqid('field'),
            'codec' => null,
            'getter' => null,
            'setter' => PropertyAccessor::createSetter('value'),
        ];

        yield 'Field without setter' => [
            'name' => uniqid('field'),
            'codec' => null,
            'getter' => PropertyAccessor::createGetter('value'),
            'setter' => null,
        ];
    }

    public function testConstructorWithoutAccessors(): void
    {
        $this->expectException(LogicException::class);

        new Field('foo', getter: null, setter: null);
    }

    private static function createDocument(): object
    {
        return new class {
            public int $value;

            public function getValue(): int
            {
                return $this->value;
            }
        };
    }
}
