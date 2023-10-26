<?php

namespace MongoDB\Bundle\ValueAccessor;

interface ValueSetter
{
    public function __invoke(object $document, mixed $value): void;
}
