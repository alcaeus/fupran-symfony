<?php

namespace MongoDB\Bundle\Tests\Codec;

use Generator;
use MongoDB\BSON\Document;
use MongoDB\BSON\PackedArray;
use MongoDB\Bundle\Codec\MappedDocumentCodec;
use MongoDB\Bundle\Metadata\Builder\DocumentBuilder;
use MongoDB\Bundle\Metadata\CodecGuesser;
use MongoDB\Bundle\Tests\Fixtures\EmbeddedDocument;
use MongoDB\Bundle\Tests\Fixtures\IntBackedEnum;
use MongoDB\Bundle\Tests\Fixtures\RootDocument;
use MongoDB\Bundle\Tests\Fixtures\StringBackedEnum;
use MongoDB\Bundle\Tests\Fixtures\UnbackedEnum;
use PHPUnit\Framework\TestCase;

/** @coversNothing */
class FieldTypeTest extends TestCase
{
    /** @dataProvider provideFields */
    public function testRoundTrippingTypes(string $fieldName, $value, $encodedValue): void
    {
        $codecGuesser = new CodecGuesser();

        $document = new RootDocument();
        $codec = new MappedDocumentCodec(DocumentBuilder::fromAttributes(RootDocument::class, $codecGuesser)->build());

        $document->$fieldName = $value;
        $encoded = $codec->encode($document);
        $this->assertTrue(isset($encoded->$fieldName));
        $this->assertEquals($encodedValue, $encoded->$fieldName);

        $decoded = $codec->decode($encoded);
        $this->assertInstanceOf(RootDocument::class, $decoded);

        $this->assertEquals($value, $decoded->$fieldName);
    }

    public static function provideFields(): Generator
    {
        yield 'string' => [
            'fieldName' => 'string',
            'value' => 'foo',
            'encodedValue' => 'foo',
        ];

        yield 'int' => [
            'fieldName' => 'int',
            'value' => 42,
            'encodedValue' => 42,
        ];

        yield 'float' => [
            'fieldName' => 'float',
            'value' => 42.42,
            'encodedValue' => 42.42,
        ];

        yield 'bool' => [
            'fieldName' => 'bool',
            'value' => true,
            'encodedValue' => true,
        ];

        yield 'array' => [
            'fieldName' => 'array',
            'value' => ['foo', 'bar'],
            'encodedValue' => PackedArray::fromPHP(['foo', 'bar']),
        ];

        yield 'object' => [
            'fieldName' => 'object',
            'value' => (object) ['foo' => 'bar'],
            'encodedValue' => Document::fromPHP(['foo' => 'bar']),
        ];

        yield 'embedded document' => [
            'fieldName' => 'embeddedDocument',
            'value' => new EmbeddedDocument('bar'),
            'encodedValue' => Document::fromPHP(['foo' => 'bar']),
        ];

        yield 'unbacked enum' => [
            'fieldName' => 'unbackedEnum',
            'value' => UnbackedEnum::BAR,
            'encodedValue' => 'BAR',
        ];

        yield 'string backed enum' => [
            'fieldName' => 'stringBackedEnum',
            'value' => StringBackedEnum::BAR,
            'encodedValue' => 'bar',
        ];

        yield 'int backed enum' => [
            'fieldName' => 'intBackedEnum',
            'value' => IntBackedEnum::BAR,
            'encodedValue' => 2,
        ];
    }
}
