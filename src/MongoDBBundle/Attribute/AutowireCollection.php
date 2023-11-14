<?php

namespace MongoDB\Bundle\Attribute;

use Exception;
use MongoDB\Bundle\Codec\MappedDocumentCodec;
use MongoDB\Bundle\DependencyInjection\MongoDBExtension;
use MongoDB\Bundle\Metadata\Document as DocumentMetadata;
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
        private ?string $collectionName = null,
        private ?string $documentClass = null,
        private array $options = [],
        bool|string $lazy = false,
    ) {
        if ($this->documentClass !== null && isset($this->options['codec'])) {
            throw new Exception('Cannot combine codec option and documentClass');
        }

        parent::__construct(
            [new Reference(MongoDBExtension::getClientServiceName($clientId)), 'selectCollection'],
            lazy: $lazy,
        );
    }

    public function buildDefinition(mixed $value, ?string $type, \ReflectionParameter $parameter): Definition
    {
        $options = $this->options;
        if ($this->documentClass) {
            $codecDefinition = new Definition(MappedDocumentCodec::class);
            $codecDefinition
                ->setFactory([new Reference('mongodb.codec.mapped_document_codec_factory'), 'getCodec'])
                ->setArguments([$this->documentClass]);

            $options['codec'] = $codecDefinition;
        }

        return (new Definition($type = \is_string($this->lazy) ? $this->lazy : ($type ?: Collection::class)))
            ->setFactory($value)
            ->setArguments([$this->databaseName, $this->collectionName ?? $parameter->getName(), $options])
            ->setLazy($this->lazy);
    }
}
