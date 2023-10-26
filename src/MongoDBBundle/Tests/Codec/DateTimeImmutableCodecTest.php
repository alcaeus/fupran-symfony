<?php

namespace MongoDB\Bundle\Tests\Codec;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use MongoDB\BSON\PackedArray;
use MongoDB\BSON\Timestamp;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Bundle\Codec\DateTimeImmutableCodec;
use PHPUnit\Framework\TestCase;

/** @covers \MongoDB\Bundle\Codec\DateTimeImmutableCodec */
final class DateTimeImmutableCodecTest extends TestCase
{
    public function testCanDecode(): void
    {
        $codec = new DateTimeImmutableCodec();

        // Only supports decoding UTCDateTime
        $this->assertTrue($codec->canDecode(new UTCDateTime()));

        // Timestamp instances and integers are not supported
        $this->assertFalse($codec->canDecode(new Timestamp(0, 0)));
        $this->assertFalse($codec->canDecode(0));
    }

    public function testCanEncode(): void
    {
        $codec = new DateTimeImmutableCodec();

        // Accepts any DateTimeInterface. Can't test directly because DateTimeInterface can't be implemented by user classes
        $this->assertTrue($codec->canEncode(new DateTimeImmutable()));
        $this->assertTrue($codec->canEncode(new DateTime()));

        $this->assertFalse($codec->canEncode(new UTCDateTime()));
        $this->assertFalse($codec->canEncode(new Timestamp(0, 0)));
        $this->assertFalse($codec->canEncode(0));
    }

    public function testDecode(): void
    {
        $utcDateTime = new UTCDateTime();
        $expected = DateTimeImmutable::createFromMutable($utcDateTime->toDateTime());

        $codec = new DateTimeImmutableCodec();

        $this->assertEquals(
            $expected,
            $codec->decode($utcDateTime),
        );
    }

    public function testEncode(): void
    {
        $dateTimeImmutable = new DateTimeImmutable();
        $expected = new UTCDateTime($dateTimeImmutable);

        $codec = new DateTimeImmutableCodec();

        $this->assertEquals(
            $expected,
            $codec->encode($dateTimeImmutable),
        );
    }
}
