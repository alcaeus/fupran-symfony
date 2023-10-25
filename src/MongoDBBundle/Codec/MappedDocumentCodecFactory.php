<?php

namespace MongoDB\Bundle\Codec;

use MongoDB\Bundle\Metadata\DocumentMetadataFactory;
use MongoDB\Codec\DocumentCodec;

class MappedDocumentCodecFactory
{
    /** @var array<string,DocumentCodec> */
    private array $codecs;

    public function __construct(
        private readonly DocumentMetadataFactory $metadataFactory,
    ) {}

    public function getCodec(string $className): DocumentCodec
    {
        return $this->codecs[$className] ??= $this->createCodec($className);
    }

    private function createCodec(string $className): DocumentCodec
    {
        return new MappedDocumentCodec($this->metadataFactory->getMetadata($className));
    }
}
