<?php

namespace MongoDB\Bundle\ValueAccessor;

interface ValueGetter
{
    public function __invoke(object $document): mixed;
}
