<?php

namespace MongoDB\Bundle\Codec;

use MongoDB\BSON\Document;
use MongoDB\Bundle\Metadata\Document as DocumentMetadata;
use MongoDB\Bundle\Metadata\Field;
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

        $object = $this->metadata->createNewInstance();

        array_map(
            fn (Field $field) => $field->readFromBSON($value, $object),
            $this->metadata->persistedFields,
        );

        return $object;
    }

    public function encode($value): Document
    {
        if (! $this->canEncode($value)) {
            throw UnsupportedValueException::invalidEncodableValue($value);
        }

        $data = [];

        array_map(
            function (Field $field) use (&$data, $value): void {
                $field->writeToBSON($data, $value);
            },
            $this->metadata->persistedFields,
        );

        return Document::fromPHP($data);
    }
}
