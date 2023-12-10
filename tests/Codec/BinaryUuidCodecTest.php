<?php

namespace App\Tests\Codec;

use App\Codec\BinaryUuidCodec;
use MongoDB\BSON\Binary;
use MongoDB\Exception\UnsupportedValueException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\UuidV1;
use Symfony\Component\Uid\UuidV4;

use function hex2bin;
use function str_replace;

final class BinaryUuidCodecTest extends TestCase
{
    private BinaryUuidCodec $codec;
    private Binary $binaryUuid;
    private UuidV4 $uuid;
    private string $stringUuid = 'b1046109-bb19-4d29-8c50-8cdee42c24bc';

    public function setUp(): void
    {
        $this->codec = new BinaryUuidCodec();
        $this->uuid = new UuidV4($this->stringUuid);
        $this->binaryUuid = new Binary(hex2bin(str_replace('-', '', $this->stringUuid)), Binary::TYPE_UUID);
    }

    public function testDecoding(): void
    {
        self::assertTrue($this->codec->canDecode($this->binaryUuid));
        self::assertEquals($this->uuid, $this->codec->decode($this->binaryUuid));
    }

    public function testDecodeChecksDecodability(): void
    {
        self::expectExceptionObject(UnsupportedValueException::invalidDecodableValue(2));
        $this->codec->decode(2);
    }

    public function testEncoding(): void
    {
        self::assertTrue($this->codec->canEncode($this->uuid));
        self::assertEquals($this->binaryUuid, $this->codec->encode($this->uuid));
    }

    public function testEncodingString(): void
    {
        self::assertTrue($this->codec->canEncode($this->stringUuid));
        self::assertEquals($this->binaryUuid, $this->codec->encode($this->stringUuid));
    }

    public function testEncodingWrongString(): void
    {
        // Int
        self::assertFalse($this->codec->canEncode(2));

        // Not a UUID string
        self::assertFalse($this->codec->canEncode('meh'));
    }

    public function testEncodeChecksEncodability(): void
    {
        self::expectExceptionObject(UnsupportedValueException::invalidEncodableValue(2));
        $this->codec->encode(2);
    }

    public function testIgnoresVariantWhenEncodingString(): void
    {
        // The following UUID uses an invalid variant (e - reserved) but should nonetheless be encodable
        $uuid = '51d4b6f1-a095-1aa0-e100-80009459e03a';
        $encoded = new Binary(hex2bin(str_replace('-', '', $uuid)), Binary::TYPE_UUID);

        self::assertTrue($this->codec->canEncode($uuid));
        self::assertEquals($encoded, $this->codec->encode($uuid));
    }
}
