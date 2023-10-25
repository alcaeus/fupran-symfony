<?php

namespace MongoDB\Bundle\Attribute;

use MongoDB\Codec\Codec;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Field
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?Codec $codec = null,
    ) {}
}
