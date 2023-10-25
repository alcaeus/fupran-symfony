<?php

namespace MongoDB\Bundle\Attribute;

use MongoDB\Bundle\DependencyInjection\MongoDBExtension;
use MongoDB\Collection;
use Symfony\Component\DependencyInjection\Attribute\AutowireCallable;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class AutowireCollection extends AutowireCallable
{
    public function __construct(
        string $clientId,
        private string $databaseName,
        private string $collectionName,
        private array $options = [],
        bool|string $lazy = false,
    ) {
        parent::__construct(
            [new Reference(MongoDBExtension::getClientServiceName($clientId)), 'selectCollection'],
            lazy: $lazy,
        );
    }

    public function buildDefinition(mixed $value, ?string $type, \ReflectionParameter $parameter): Definition
    {
        return (new Definition($type = \is_string($this->lazy) ? $this->lazy : ($type ?: Collection::class)))
            ->setFactory($value)
            ->setArguments([$this->databaseName, $this->collectionName, $this->options])
            ->setLazy($this->lazy);
    }
}
