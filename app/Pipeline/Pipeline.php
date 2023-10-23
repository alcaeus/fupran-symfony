<?php

namespace App\Pipeline;

interface Pipeline
{
    /** @return array<object> */
    public function getPipeline(): array;
}
