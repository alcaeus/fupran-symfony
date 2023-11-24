<?php

namespace App\Aggregation;

use MongoDB\Builder\Pipeline;

interface Aggregation
{
    public function getPipeline(): Pipeline;
}
