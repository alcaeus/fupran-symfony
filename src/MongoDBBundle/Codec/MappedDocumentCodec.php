<?php

namespace MongoDB\Bundle\Codec;

use MongoDB\BSON\Document;
use MongoDB\Bundle\Metadata\Document as DocumentMetadata;
use MongoDB\Codec\Codec;
use MongoDB\Codec\DecodeIfSupported;
use MongoDB\Codec\DocumentCodec;
use MongoDB\Codec\EncodeIfSupported;
use MongoDB\Exception\UnsupportedValueException;
use ReflectionClass;

class MappedDocumentCodec implements DocumentCodec
{
    use DecodeIfSupported;
    use EncodeIfSupported;

    private ReflectionClass $reflection;

    public function __construct(
        private readonly DocumentMetadata $metadata,
    ) {
        $this->reflection = new ReflectionClass($this->metadata->className);
    }

    public function canDecode($value): bool
    {
        return $value instanceof Document;
    }

    public function canEncode($value): bool
    {
        return $value instanceof $this->metadata->className;
    }

    public function decode($value): object
    {
        if (! $this->canDecode($value)) {
            throw UnsupportedValueException::invalidDecodableValue($value);
        }

        $object = $this->metadata->getNewInstance();

        foreach ($this->metadata->fieldMappings as $fieldMapping) {
            if (! $fieldMapping->existsInBson($value)) {
                continue;
            }

            $decodedValue = $fieldMapping->readFromBson($value);

            $this->reflection->getProperty($fieldMapping->propertyName)->setValue($object, $decodedValue);
        }

        return $object;
    }

    public function encode($value): Document
    {
        if (! $this->canEncode($value)) {
            throw UnsupportedValueException::invalidEncodableValue($value);
        }

        $data = [];

        foreach ($this->metadata->fieldMappings as $fieldMapping) {
            $propertyValue = $this->reflection->getProperty($fieldMapping->propertyName)->getValue($value);
            if ($propertyValue === null) {
                continue;
            }

            $encodedValue = $fieldMapping->codec
                ? $fieldMapping->codec->encode($propertyValue)
                : $propertyValue;

            if ($encodedValue !== null) {
                $data[$fieldMapping->propertyName] = $encodedValue;
            }
        }

        return Document::fromPHP($data);
    }
}
