<?php

namespace MongoDB\Bundle\Tests\Metadata;

use MongoDB\Bundle\Codec\ArrayCodec;
use MongoDB\Bundle\Codec\ObjectCodec;
use MongoDB\Bundle\Metadata\CodecGuesser;
use Generator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionType;
use stdClass;

/** @covers \MongoDB\Bundle\Metadata\CodecGuesser */
final class CodecGuesserTest extends TestCase
{
    private static ReflectionClass $reflectionClass;

    /** @dataProvider provideTypes */
    public function testGuessCodec(ReflectionType $type, ?string $expectedCodecClass): void
    {
        $guesser = new CodecGuesser();
        $codec = $guesser->guessCodec($type);

        if ($expectedCodecClass === null) {
            $this->assertNull($codec);
        } else {
            $this->assertInstanceOf($expectedCodecClass, $codec);
        }
    }

    public static function provideTypes(): Generator
    {
        yield 'array' => [
            'type' => self::getReflectionType('array'),
            'codec' => ArrayCodec::class,
        ];

        yield 'stdClass' => [
            'type' => self::getReflectionType('stdClass'),
            'codec' => ObjectCodec::class,
        ];
    }

    public static function getReflectionType(string $typeName): ReflectionType
    {
        self::$reflectionClass ??= new ReflectionClass(TypeHolder::class);

        return self::$reflectionClass->getProperty($typeName)->getType();
    }
}

class TypeHolder
{
    public static array $array;
    public static stdClass $stdClass;
}
