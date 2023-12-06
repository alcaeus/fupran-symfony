<?php

namespace App\Tests\Document;

use App\Document\Station;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\UuidV4;

final class StationTest extends TestCase
{
    public function testCreateWithoutIdentifier(): void
    {
        $station = new Station();
        self::assertInstanceOf(UuidV4::class, $station->id);
    }

    public function testCreateWithStringIdentifier(): void
    {
        $uuid = 'b1046109-bb19-4d29-8c50-8cdee42c24bc';
        $station = new Station($uuid);
        self::assertSame($uuid, (string) $station->id);
    }

    public function testCreateWithUuid(): void
    {
        $uuid = 'b1046109-bb19-4d29-8c50-8cdee42c24bc';
        $station = new Station(new UuidV4($uuid));
        self::assertSame($uuid, (string) $station->id);
    }
}
