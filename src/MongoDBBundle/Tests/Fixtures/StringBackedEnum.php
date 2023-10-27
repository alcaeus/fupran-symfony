<?php

namespace MongoDB\Bundle\Tests\Fixtures;

enum StringBackedEnum: string
{
    case FOO = 'foo';
    case BAR = 'bar';
    case BAZ = 'baz';
}
