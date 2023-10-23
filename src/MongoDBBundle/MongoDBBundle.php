<?php

namespace MongoDB\Bundle;

use MongoDB\Bundle\DependencyInjection\MongoDBExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class MongoDBBundle extends AbstractBundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new MongoDBExtension();
    }
}
