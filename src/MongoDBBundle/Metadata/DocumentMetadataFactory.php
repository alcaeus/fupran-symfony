<?php

namespace MongoDB\Bundle\Metadata;

class DocumentMetadataFactory
{
    /** @var array<string,Document> */
    private array $metadata;

    public function getMetadata(string $className): Document
    {
        return $this->metadata[$className] ??= $this->loadMetadata($className);
    }

    private function loadMetadata(string $className): Document
    {
        // TODO: Create different readers
        return Document::fromAttributes($className);
    }
}
