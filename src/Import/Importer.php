<?php

namespace App\Import;

use DirectoryIterator;
use MongoDB\BSON\Binary;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use Symfony\Component\Console\Output\OutputInterface;
use function hex2bin;
use function iterator_to_array;
use function str_replace;
use function usort;

abstract class Importer
{
    public function __construct(
        private readonly Manager $manager,
        protected readonly string $databaseName,
    ) {
    }

    abstract protected function storeDocument(BulkWrite $bulk, array $data): void;

    abstract protected function getNamespace(): string;

    final public function importDirectory(string $directory, ?OutputInterface $output = null): ImportResult
    {
        $result = new ImportResult();

        $iterator = new DirectoryIterator($directory);
        foreach ($iterator as $file) {
            if ($file->isDot()) {
                continue;
            }

            if ($file->isDir()) {
                $result = $result->withResult($this->importDirectory($file->getPathname(), $output));

                continue;
            }

            if ($file->getExtension() !== 'csv') {
                continue;
            }

            $result = $result->withResult($this->importFile($file->getPathname(), $output));
        }

        return $result;
    }

    final public function importFile(string $file, ?OutputInterface $output = null): ImportResult
    {
        $output?->writeln(sprintf('Importing file "%s"', $file));

        $resource = fopen($file, 'r');
        if (!$resource) {
            throw new \RuntimeException(sprintf('Could not read file "%s"', $file));
        }

        $bulk = new BulkWrite(['ordered' => false]);

        try {
            $headers = fgetcsv($resource);

            while ($row = fgetcsv($resource)) {
                $this->storeDocument($bulk, array_combine($headers, $row));
            }
        } finally {
            fclose($resource);

            $output?->writeln(sprintf('Read %d records, importing now', $bulk->count()));

            $writeResult = $this->manager->executeBulkWrite($this->getNamespace(), $bulk);
            $importResult = ImportResult::fromWriteResult($writeResult);

            $output?->writeln(sprintf('Inserted %d records, skipped %d records', $importResult->numInserted, $importResult->numSkipped));

            return $importResult;
        }
    }

    protected function createBinaryUuid(string $uuid): Binary
    {
        return new Binary(hex2bin(str_replace('-', '', $uuid)), Binary::TYPE_UUID);
    }
}
