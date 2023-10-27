<?php

namespace MongoDB\Bundle\Tests\Codec;

use MongoDB\BSON\Document;
use MongoDB\Bundle\Codec\ObjectCodec;
use PHPUnit\Framework\TestCase;

/** @covers \MongoDB\Bundle\Codec\ObjectCodec */
final class ObjectCodecTest extends TestCase
{
    public function testCanDecode(): void
    {
        $codec = new ObjectCodec();

        $this->assertTrue($codec->canDecode(Document::fromPHP([])));
        $this->assertFalse($codec->canDecode([]));
    }

    public function testCanEncode(): void
    {
        $codec = new ObjectCodec();

        $this->assertTrue($codec->canEncode((object) ['foo' => 'bar']));

        $this->assertFalse($codec->canEncode([]));
        $this->assertFalse($codec->canEncode(['foo' => 'bar']));
        $this->assertFalse($codec->canEncode([0, 1, 2]));
    }

    public function testDecode(): void
    {
        $object = (object) ['foo' => 'bar'];

        $codec = new ObjectCodec();

        $this->assertEquals(
            $object,
            $codec->decode(Document::fromPHP($object)),
        );
    }

    public function testEncode(): void
    {
        $object = (object) ['foo' => 'bar'];

        $codec = new ObjectCodec();

        $this->assertEquals(
            Document::fromPHP($object),
            $codec->encode($object),
        );
    }
}
