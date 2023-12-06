<?php

namespace App\Tests\Codec;

use App\Codec\AddressCodec;
use App\Codec\BinaryUuidCodec;
use App\Codec\PointCodec;
use App\Codec\StationCodec;
use App\Document\Address;
use App\Document\Station;
use GeoJson\Geometry\Point;
use MongoDB\BSON\Binary;
use MongoDB\BSON\Document;
use PHPUnit\Framework\TestCase;

use function hex2bin;
use function str_replace;

final class StationCodecTest extends TestCase
{
    private StationCodec $codec;
    private Document $stationDocument;
    private Station $station;

    public function setUp(): void
    {
        $this->codec = new StationCodec(
            new BinaryUuidCodec(),
            new AddressCodec(),
            new PointCodec(),
        );

        $uuid = 'b1046109-bb19-4d29-8c50-8cdee42c24bc';
        $this->station = new Station($uuid);

        $this->station->name = 'Service Station';
        $this->station->brand = 'Unbranded';
        $this->station->address = new Address(street: 'SomeStreet', houseNumber: '15a', postCode: '12345', city: 'SomeCity');
        $this->station->location = new Point([1.2, 2.4]);

        $this->stationDocument = Document::fromPHP([
            '_id' => new Binary(hex2bin(str_replace('-', '', $uuid)), Binary::TYPE_UUID),
            'name' => 'Service Station',
            'brand' => 'Unbranded',
            'address' => [
                'street' => 'SomeStreet',
                'houseNumber' => '15a',
                'postCode' => '12345',
                'city' => 'SomeCity',
            ],
            'location' => [
                'type' => 'Point',
                'coordinates' => [1.2, 2.4],
            ],
        ]);
    }

    public function testDecoding(): void
    {
        self::assertTrue($this->codec->canDecode($this->stationDocument));
        self::assertEquals($this->station, $this->codec->decode($this->stationDocument));
    }

    public function testEncoding(): void
    {
        self::assertTrue($this->codec->canEncode($this->station));
        self::assertEquals($this->stationDocument, $this->codec->encode($this->station));
    }
}
