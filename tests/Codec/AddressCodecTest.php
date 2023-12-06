<?php

namespace App\Tests\Codec;

use App\Codec\AddressCodec;
use App\Document\Address;
use MongoDB\BSON\Document;
use PHPUnit\Framework\TestCase;

final class AddressCodecTest extends TestCase
{
    private AddressCodec $codec;
    private Document $addressDocument;
    private Address $address;

    public function setUp(): void
    {
        $this->codec = new AddressCodec();
        $this->address = new Address(street: 'SomeStreet', houseNumber: '15a', postCode: '12345', city: 'SomeCity');
        $this->addressDocument = Document::fromPHP([
            'street' => 'SomeStreet',
            'houseNumber' => '15a',
            'postCode' => '12345',
            'city' => 'SomeCity',
        ]);
    }

    public function testDecoding(): void
    {
        self::assertTrue($this->codec->canDecode($this->addressDocument));
        self::assertEquals($this->address, $this->codec->decode($this->addressDocument));
    }

    public function testEncoding(): void
    {
        self::assertTrue($this->codec->canEncode($this->address));
        self::assertEquals($this->addressDocument, $this->codec->encode($this->address));
    }
}
