<?php

namespace MongoDB\Bundle\Tests\Codec;

use Generator;
use MongoDB\BSON\Document;
use MongoDB\Bundle\Codec\MappedDocumentCodec;
use MongoDB\Bundle\Metadata\Builder\DocumentBuilder;
use MongoDB\Bundle\Metadata\Document as DocumentMetadata;
use MongoDB\Bundle\Tests\Fixtures\SimpleTestDocument;
use PHPUnit\Framework\TestCase;

/** @covers \MongoDB\Bundle\Codec\MappedDocumentCodec */
final class MappedDocumentCodecTest extends TestCase
{
    /** @dataProvider provideMetadata */
    public function testDecode(DocumentMetadata $metadata): void
    {
        $expected = new SimpleTestDocument(5);
        $bsonDocument = Document::fromPHP([
            '_id' => $expected->id,
            'value' => 5,
            'square' => 25,
            'unused' => 'foo',
        ]);

        $codec = $this->createCodec($metadata);
        $this->assertTrue($codec->canDecode($bsonDocument));

        $decoded = $codec->decode($bsonDocument);

        $this->assertEquals($expected, $decoded);
    }

    /** @dataProvider provideMetadata */
    public function testEncode(DocumentMetadata $metadata): void
    {
        $document = new SimpleTestDocument(5);

        $codec = $this->createCodec($metadata);
        $this->assertTrue($codec->canEncode($document));

        $encoded = $codec->encode($document);

        $this->assertEquals(
            (object) [
                '_id' => $document->id,
                'value' => 5,
                'square' => 25,
            ],
            $encoded->toPHP(),
        );
    }

    public static function provideMetadata(): Generator
    {
        yield 'fromReflection' => [
            SimpleTestDocument::getMetadata(),
        ];

        yield 'fromAttributes' => [
            DocumentBuilder::fromAttributes(SimpleTestDocument::class)->build(),
        ];
    }

    private function createCodec(DocumentMetadata $metadata): MappedDocumentCodec
    {
        return new MappedDocumentCodec($metadata);
    }
}
