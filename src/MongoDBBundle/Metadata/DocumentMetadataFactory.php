<?php

namespace MongoDB\Bundle\Metadata;

use MongoDB\Bundle\Metadata\Builder\DocumentBuilder;
use Throwable;

class DocumentMetadataFactory
{
    /** @var array<string,Document> */
    private array $metadata;

    public function getMetadata(string $className): Document
    {
        return $this->metadata[$className] ??= $this->loadMetadata($className);
    }

    public function isMappedDocument(string $className): bool
    {
        // TODO: don't blindly load but find a different way to check
        try {
            $this->getMetadata($className);
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function loadMetadata(string $className): Document
    {
        // TODO: Create different readers
        return DocumentBuilder::fromAttributes($className)->build();
    }
}
