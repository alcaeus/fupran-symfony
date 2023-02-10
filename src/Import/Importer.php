<?php

namespace App\Import;

use DirectoryIterator;
use MongoDB\BSON\Binary;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use function hex2bin;
use function str_replace;

abstract class Importer
{
    public function __construct(
        private readonly Manager $manager,
        protected readonly string $databaseName,
    ) {
    }

    abstract protected function storeDocument(BulkWrite $bulk, array $data): void;

    abstract protected function getNamespace(): string;

    final public function importDirectory(string $directory): ImportResult
    {
        $result = new ImportResult();

        $iterator = new DirectoryIterator($directory);
        foreach ($iterator as $file) {
            if ($file->isDot()) {
                continue;
            }

            if ($file->isDir()) {
                $result = $result->withResult($this->importDirectory($file->getPathname()));

                continue;
            }

            if ($file->getExtension() !== 'csv') {
                continue;
            }

            $result = $result->withResult($this->importFile($file->getPathname()));
        }

        return $result;
    }

    final public function importFile(string $file): ImportResult
    {
        $resource = fopen($file, 'r');
        if (!$resource) {
            throw new \RuntimeException(sprintf('Could not read file "%s"', $file));
        }

        try {
            $headers = fgetcsv($resource);
            $bulk = new BulkWrite(['ordered' => false]);

            while ($row = fgetcsv($resource)) {
                $this->storeDocument($bulk, array_combine($headers, $row));
            }
        } finally {
            fclose($resource);

            $writeResult = $this->manager->executeBulkWrite($this->getNamespace(), $bulk);
            return ImportResult::fromWriteResult($writeResult);
        }
    }

    protected function createBinaryUuid(string $uuid): Binary
    {
        return new Binary(hex2bin(str_replace('-', '', $uuid)), Binary::TYPE_UUID);
    }
}
