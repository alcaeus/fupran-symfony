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
    public readonly ?string $fieldName;

    public function __construct(
        public readonly string $propertyName,
        ?string $fieldName = null,
        public readonly ?Codec $codec = null,
    ) {
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
            $property->getName(),
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
}
