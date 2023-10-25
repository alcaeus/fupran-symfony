<?php

namespace MongoDB\Bundle\Attribute;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class AutowireClient extends Autowire
{
    public function __construct(
        string $clientId,
        bool|string $lazy = false,
    ) {
        parent::__construct(service: 'mongodb.client.' . $clientId, lazy: $lazy);
    }
}
