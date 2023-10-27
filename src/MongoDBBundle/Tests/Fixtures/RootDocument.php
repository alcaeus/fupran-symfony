<?php

namespace MongoDB\Bundle\Tests\Fixtures;

use MongoDB\BSON\ObjectId;
use MongoDB\Bundle\Attribute\Document;
use MongoDB\Bundle\Attribute\Field;
use stdClass;

#[Document]
class RootDocument
{
    #[Field(name: '_id')]
    public readonly ObjectId $id;

    #[Field]
    public string $string;

    #[Field]
    public int $int;

    #[Field]
    public float $float;

    #[Field]
    public bool $bool;

    #[Field]
    public array $array;

    #[Field]
    public stdClass $object;

    #[Field]
    public EmbeddedDocument $embeddedDocument;

    #[Field]
    public UnbackedEnum $unbackedEnum;

    #[Field]
    public StringBackedEnum $stringBackedEnum;

    #[Field]
    public IntBackedEnum $intBackedEnum;
}
