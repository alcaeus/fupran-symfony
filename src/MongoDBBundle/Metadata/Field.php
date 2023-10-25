<?php

namespace MongoDB\Bundle\Metadata;

use MongoDB\BSON\Document as BSONDocument;
use MongoDB\Codec\Codec;

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
