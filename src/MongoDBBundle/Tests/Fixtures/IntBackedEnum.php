<?php

namespace MongoDB\Bundle\Tests\Fixtures;

enum IntBackedEnum: int
{
    case FOO = 1;
    case BAR = 2;
    case BAZ = 3;
}
