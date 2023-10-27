<?php

namespace MongoDB\Bundle\Tests\Codec;

use Generator;
use MongoDB\Bundle\Codec\EnumCodec;
use MongoDB\Bundle\Tests\Fixtures\IntBackedEnum;
use MongoDB\Bundle\Tests\Fixtures\StringBackedEnum;
use MongoDB\Bundle\Tests\Fixtures\UnbackedEnum;
use PHPUnit\Framework\TestCase;
use ValueError;

/** @covers \MongoDB\Bundle\Codec\EnumCodec */
final class EnumCodecTest extends TestCase
{
    /** @dataProvider provideEnums */
    public function testCanDecode(string $enumClass): void
    {
        $codec = new EnumCodec($enumClass);

        // Can decode any integer or string, regardless of backing type
        $this->assertTrue($codec->canDecode(1));
        $this->assertTrue($codec->canDecode('foo'));
    }

    public function testCanEncode(): void
    {
        $codec = new EnumCodec(StringBackedEnum::class);

        $this->assertTrue($codec->canEncode(StringBackedEnum::BAR));

        // Only values of the given enum type are supported
        $this->assertFalse($codec->canEncode(IntBackedEnum::BAR));
    }

    /** @dataProvider provideEnums */
    public function testDecode(string $enumClass, $enumValue, $encodedValue): void
    {
        $codec = new EnumCodec($enumClass);

        $this->assertEquals(
            $enumValue,
            $codec->decode($encodedValue),
        );
    }

    /** @dataProvider provideEnums */
    public function testDecodeInvalidValue(string $enumClass): void
    {
        $codec = new EnumCodec($enumClass);

        $this->expectException(ValueError::class);
        $codec->decode('invalid');
    }

    /** @dataProvider provideEnums */
    public function testEncode(string $enumClass, $enumValue, $encodedValue): void
    {
        $codec = new EnumCodec($enumClass);

        $this->assertEquals(
            $encodedValue,
            $codec->encode($enumValue),
        );
    }

    public static function provideEnums(): Generator
    {
        yield 'unbacked enum' => [
            'enumClass' => UnbackedEnum::class,
            'enumValue' => UnbackedEnum::BAR,
            'encodedValue' => 'BAR',
        ];

        yield 'string backed enum' => [
            'enumClass' => StringBackedEnum::class,
            'enumValue' => StringBackedEnum::BAR,
            'encodedValue' => 'bar',
        ];

        yield 'int backed enum' => [
            'enumClass' => IntBackedEnum::class,
            'enumValue' => IntBackedEnum::BAR,
            'encodedValue' => 2,
        ];
    }
}
