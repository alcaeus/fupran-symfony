<?php

namespace MongoDB\Bundle\Tests\Codec;

use MongoDB\BSON\Document;
use MongoDB\Bundle\Codec\MappedDocumentCodec;
use MongoDB\Bundle\Tests\Fixtures\SimpleTestDocument;
use PHPUnit\Framework\TestCase;

/** @covers \MongoDB\Bundle\Codec\MappedDocumentCodec */
final class MappedDocumentCodecTest extends TestCase
{
    public function testDecode(): void
    {
        $expected = new SimpleTestDocument(5);
        $bsonDocument = Document::fromPHP([
            '_id' => $expected->id,
            'value' => 5,
            'square' => 25,
            'unused' => 'foo',
        ]);

        $codec = $this->createCodec();
        $this->assertTrue($codec->canDecode($bsonDocument));

        $decoded = $codec->decode($bsonDocument);

        $this->assertEquals($expected, $decoded);
    }

    public function testEncode(): void
    {
        $document = new SimpleTestDocument(5);

        $codec = $this->createCodec();
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

    private function createCodec(): MappedDocumentCodec
    {
        return new MappedDocumentCodec(SimpleTestDocument::getMetadata());
    }
}
