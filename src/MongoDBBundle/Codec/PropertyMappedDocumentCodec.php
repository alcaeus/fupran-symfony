<?php

namespace MongoDB\Bundle\Codec;

use MongoDB\BSON\Document;
use MongoDB\Codec\Codec;
use MongoDB\Codec\DecodeIfSupported;
use MongoDB\Codec\DocumentCodec;
use MongoDB\Codec\EncodeIfSupported;
use MongoDB\Exception\UnsupportedValueException;
use ReflectionClass;

class PropertyMappedDocumentCodec implements DocumentCodec
{
    use DecodeIfSupported;
    use EncodeIfSupported;

    private ReflectionClass $reflection;

    /** @var array<string,?Codec> */
    private array $propertyMap;

    public function __construct(
        private readonly string $className,
        ?Codec ...$propertyMap,
    ) {
        $this->reflection = new ReflectionClass($className);
        $this->propertyMap = $propertyMap;
    }

    public function canDecode($value): bool
    {
        return $value instanceof Document;
    }

    public function canEncode($value): bool
    {
        return $value instanceof $this->className;
    }

    public function decode($value): object
    {
        if (! $this->canDecode($value)) {
            throw UnsupportedValueException::invalidDecodableValue($value);
        }

        $object = $this->reflection->newInstanceWithoutConstructor();

        foreach ($this->propertyMap as $property => $codec) {
            if (! isset($value->$property)) {
                continue;
            }

            $decodedValue = $codec
                ? $codec->decode($value->$property)
                : $value->$property;

            $this->reflection->getProperty($property)->setValue($object, $decodedValue);
        }

        return $object;
    }

    public function encode($value): Document
    {
        if (! $this->canEncode($value)) {
            throw UnsupportedValueException::invalidEncodableValue($value);
        }

        $data = [];

        foreach ($this->propertyMap as $property => $codec) {
            $value = $this->reflection->getProperty($property)->getValue($value);
            if ($value === null) {
                continue;
            }

            $encodedValue = $codec
                ? $codec->encode($value->$property)
                : $value->$property;

            if ($encodedValue !== null) {
                $data[$property] = $encodedValue;
            }
        }

        return Document::fromPHP($data);
    }
}
