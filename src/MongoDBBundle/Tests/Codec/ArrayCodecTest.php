<?php

namespace MongoDB\Bundle\Tests\Codec;

use MongoDB\BSON\PackedArray;
use MongoDB\Bundle\Codec\ArrayCodec;
use MongoDB\Codec\Codec;
use MongoDB\Codec\DecodeIfSupported;
use MongoDB\Codec\EncodeIfSupported;
use PHPUnit\Framework\TestCase;

/** @covers \MongoDB\Bundle\Codec\ArrayCodec */
final class ArrayCodecTest extends TestCase
{
    public function testCanDecode(): void
    {
        $codec = new ArrayCodec();

        $this->assertTrue($codec->canDecode(PackedArray::fromPHP([])));
        $this->assertFalse($codec->canDecode([]));
    }

    public function testCanEncode(): void
    {
        $codec = new ArrayCodec();

        $this->assertTrue($codec->canEncode([]));
        $this->assertTrue($codec->canEncode([0, 1, 2]));

        // Only lists are supported
        $this->assertFalse($codec->canEncode(['foo' => 'bar']));
        $this->assertFalse($codec->canEncode(PackedArray::fromPHP([])));
    }

    public function testDecode(): void
    {
        $values = ['great', 'success'];

        $codec = new ArrayCodec();

        $this->assertEquals(
            $values,
            $codec->decode(PackedArray::fromPHP($values)),
        );
    }

    public function testDecodeWithCodec(): void
    {
        $values = ['taerg', 'sseccus'];

        $codec = new ArrayCodec($this->getCodec());

        $this->assertEquals(
            ['great', 'success'],
            $codec->decode(PackedArray::fromPHP($values)),
        );
    }

    public function testEncode(): void
    {
        $values = ['great', 'success'];

        $codec = new ArrayCodec();

        $this->assertEquals(
            PackedArray::fromPHP($values),
            $codec->encode($values),
        );
    }

    public function testEncodeWithCodec(): void
    {
        $values = ['taerg', 'sseccus'];

        $codec = new ArrayCodec($this->getCodec());

        $this->assertEquals(
            PackedArray::fromPHP(['great', 'success']),
            $codec->encode($values),
        );
    }

    private function getCodec(): Codec
    {
        return new class implements Codec {
            use DecodeIfSupported;
            use EncodeIfSupported;

            public function canDecode($value): bool
            {
                return is_string($value);
            }

            public function canEncode($value): bool
            {
                return is_string($value);
            }

            public function decode($value)
            {
                return strrev($value);
            }

            public function encode($value)
            {
                return strrev($value);
            }
        };
    }
}
