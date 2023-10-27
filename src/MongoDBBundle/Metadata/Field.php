<?php

namespace MongoDB\Bundle\Metadata;

use MongoDB\BSON\Document as BSONDocument;
use MongoDB\Bundle\ValueAccessor\ValueGetter;
use MongoDB\Bundle\ValueAccessor\ValueSetter;
use LogicException;
use MongoDB\Codec\Codec;

final class Field
{
    public function __construct(
        public readonly string $name,
        public readonly ?Codec $codec = null,
        public readonly ?ValueGetter $getter = null,
        public readonly ?ValueSetter $setter = null,
    ) {
        if (!$this->getter && !$this->setter) {
            throw new LogicException('Cannot build field without either getter or setter!');
        }
    }

    public function getPHPValue(object $document): mixed
    {
        return $this->getter?->__invoke($document);
    }

    public function readFromBSON(BSONDocument $bson, object $document): void
    {
        if (! $this->setter) {
            return;
        }

        if (! isset($bson->{$this->name})) {
            return;
        }

        $bsonValue = $bson->{$this->name};
        if ($bsonValue === null) {
            return;
        }

        $this->setPHPValue($document, $this->decodeBSONValue($bsonValue));
    }

    public function setPHPValue(object $document, mixed $value): void
    {
        $this->setter?->__invoke($document, $value);
    }

    public function writeToBSON(array &$bson, object $document): void
    {
        if (! $this->getter) {
            return;
        }

        $phpValue = $this->getPHPValue($document);
        if ($phpValue === null) {
            return;
        }

        $bsonValue = $this->encodePHPValue($phpValue);
        if ($bsonValue === null) {
            return;
        }

        $bson[$this->name] = $bsonValue;
    }

    private function decodeBSONValue(mixed $value): mixed
    {
        return $this->codec
            ? $this->codec->decode($value)
            : $value;
    }

    private function encodePHPValue(mixed $value): mixed
    {
        return $this->codec
            ? $this->codec->encode($value)
            : $value;
    }
}
