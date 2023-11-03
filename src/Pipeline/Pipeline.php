<?php

namespace App\Pipeline;

interface Pipeline
{
    public function getPipeline(): \MongoDB\Builder\Pipeline;
}
