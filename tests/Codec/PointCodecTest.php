<?php

namespace App\Tests\Codec;

use App\Codec\PointCodec;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\Point;
use MongoDB\BSON\Document;
use PHPUnit\Framework\TestCase;

final class PointCodecTest extends TestCase
{
    private PointCodec $codec;
    private Document $pointDocument;
    private Point $point;

    public function setUp(): void
    {
        $this->codec = new PointCodec();
        $this->point = new Point([1.2, 2.4]);
        $this->pointDocument = Document::fromPHP([
            'type' => 'Point',
            'coordinates' => [1.2, 2.4],
        ]);
    }

    public function testDecoding(): void
    {
        self::assertTrue($this->codec->canDecode($this->pointDocument));
        self::assertEquals($this->point, $this->codec->decode($this->pointDocument));
    }

    public function testEncoding(): void
    {
        self::assertTrue($this->codec->canEncode($this->point));
        self::assertEquals($this->pointDocument, $this->codec->encode($this->point));
    }

    public function testOnlyPointDocumentsCanBeDecoded(): void
    {
        // Not a document
        self::assertFalse(
            $this->codec->canDecode($this->point),
        );

        // No "type" field
        self::assertFalse(
            $this->codec->canDecode(
                Document::fromPHP([]),
            ),
        );

        // Wrong type
        self::assertFalse(
            $this->codec->canDecode(
                Document::fromPHP(['type' => 'LineString']),
            ),
        );
    }

    public function testOnlyPointCanBeEncoded(): void
    {
        self::assertFalse(
            $this->codec->canEncode(
                new LineString([[0, 0], [1, 1]]),
            ),
        );
    }
}
