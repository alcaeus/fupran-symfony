<?php

namespace MongoDB\Bundle\Metadata;

use MongoDB\BSON\Document as BSONDocument;
use MongoDB\Bundle\Attribute\Field as FieldAttribute;
use MongoDB\Bundle\Codec\MappedDocumentCodec;
use MongoDB\Codec\Codec;
use ReflectionNamedType;
use ReflectionProperty;

final class Field
{
    public readonly string $propertyName;
    public readonly string $fieldName;

    public function __construct(
        private readonly ReflectionProperty $property,
        ?string $fieldName = null,
        private readonly ?Codec $codec = null,
    ) {
        $this->propertyName = $this->property->getName();
        $this->fieldName = $fieldName ?? $this->propertyName;
    }

    public static function fromAttributes(ReflectionProperty $property, DocumentMetadataFactory $metadataFactory): ?self
    {
        $attributes = $property->getAttributes(FieldAttribute::class);
        if (! $attributes) {
            return null;
        }

        $attribute = $attributes[0]->newInstance();

        $codec = null;

        if ($attribute->codec === null) {
            // Try to guess codec by type
            $type = $property->getType();
            if ($type instanceof ReflectionNamedType && $metadataFactory->isMappedDocument($type->getName())) {
                $codec = new MappedDocumentCodec($metadataFactory->getMetadata($type->getName()));
            }
        }

        return new self(
            $property,
            $attribute->name,
            $codec ?? $attribute->codec,
        );
    }

    public function existsInBson(BSONDocument $bson): bool
    {
        return $bson->has($this->fieldName);
    }

    public function readFromBson(BSONDocument $bson): mixed
    {
        if (! $this->existsInBson($bson)) {
            return null;
        }

        $bsonValue = $bson->get($this->fieldName);

        return $this->codec
            ? $this->codec->decode($bsonValue)
            : $bsonValue;
    }

    public function writeToBson(array &$bson, mixed $value): void
    {
        $bsonValue = $this->codec
            ? $this->codec->encode($value)
            : $value;

        if ($bsonValue !== null) {
            $bson[$this->fieldName] = $bsonValue;
        }
    }

    public function readFromObject(object $object): mixed
    {
        return $this->property->getValue($object);
    }

    public function writeToObject(object $object, mixed $value): void
    {
        $this->property->setValue($object, $value);
    }
}
