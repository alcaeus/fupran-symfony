<?php

namespace App\Tests\Codec;

use App\Codec\BinaryUuidCodec;
use MongoDB\BSON\Binary;
use PHPUnit\Framework\TestCase;
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

    public function testEncoding(): void
    {
        self::assertTrue($this->codec->canEncode($this->uuid));
        self::assertEquals($this->binaryUuid, $this->codec->encode($this->uuid));
    }
}
