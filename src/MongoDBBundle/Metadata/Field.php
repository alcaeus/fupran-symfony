<?php

namespace MongoDB\Bundle\Metadata;

use MongoDB\BSON\Document as BSONDocument;
use MongoDB\Bundle\Attribute\Field as FieldAttribute;
use MongoDB\Codec\Codec;
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

    public static function fromAttributes(ReflectionProperty $property): ?self
    {
        $attributes = $property->getAttributes(FieldAttribute::class);
        if (! $attributes) {
            return null;
        }

        $attribute = $attributes[0]->newInstance();

        return new self(
            $property->getName(),
            $attribute->name,
            $attribute->codec,
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
