<?php

namespace MongoDB\Bundle\Tests\Fixtures;

use MongoDB\Bundle\Attribute\Document;
use MongoDB\Bundle\Attribute\Field;

#[Document]
class EmbeddedDocument
{
    public function __construct(
        #[Field]
        public string $foo,
    ) {}
}
