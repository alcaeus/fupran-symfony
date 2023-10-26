<?php

namespace MongoDB\Bundle\Codec;

use MongoDB\BSON\Document;
use MongoDB\Bundle\Metadata\Document as DocumentMetadata;
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
            $fieldMapping->writeToObject($object, $decodedValue);
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
            $propertyValue = $fieldMapping->readFromObject($value);
            if ($propertyValue === null) {
                continue;
            }

            $fieldMapping->writeToBson($data, $propertyValue);
        }

        return Document::fromPHP($data);
    }
}
