<?php

namespace App\Command;

use App\Pipeline\GetPriceReports;
use MongoDB\Collection;
use MongoDB\Database;
use MongoDB\Model\IndexInfo;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function iterator_to_array;

#[AsCommand(
    name: 'app:generate-schema',
    description: 'Add a short description for your command',
)]
class GenerateSchemaCommand extends Command
{
    public function __construct(
        private readonly Database $database,
        private readonly GetPriceReports $getPriceReports,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->ensureStationIndexes($io);
        $this->ensurePriceIndexes($io);
        $this->ensurePriceReportIndexes($io);
        $this->createPriceReportView($io);

        $io->success('Schema is up to date!');

        return Command::SUCCESS;
    }

    private function ensureIndexes(Collection $collection, array $expectedIndexes, StyleInterface $output): void
    {
        $existingIndexes = array_map(
            fn (IndexInfo $index) => $index->getName(),
            iterator_to_array($collection->listIndexes()),
        );

        $missingIndexes = array_diff_key($expectedIndexes, array_flip($existingIndexes));

        foreach ($missingIndexes as $name => $index) {
            $indexOptions = $index['options'] ?? [];
            $output->text(sprintf('Creating index "%s.%s"...', $collection->getCollectionName(), $name));
            $collection->createIndex($index['key'], $indexOptions + ['name' => $name]);
            $output->text('Done!');
        }
    }

    private function ensureStationIndexes(StyleInterface $output): void
    {
        $expectedIndexes = [
            'brand' => ['key' => ['brand' => 1]],
            'postCode' => ['key' => ['address.postCode' => 1]],
        ];

        $this->ensureIndexes(
            $this->database->selectCollection('stations'),
            $expectedIndexes,
            $output,
        );
    }

    private function ensurePriceIndexes(StyleInterface $output): void
    {
        $expectedIndexes = [
            'reportDate' => ['key' => ['reportDate' => 1]],
            'station' => ['key' => ['station' => 1]],
        ];

        $this->ensureIndexes(
            $this->database->selectCollection('prices'),
            $expectedIndexes,
            $output,
        );
    }

    private function ensurePriceReportIndexes(StyleInterface $output): void
    {
        $expectedIndexes = [
            // This key is used by the $merge stage when materialising price reports
            'reportDate_fuelType_station' => [
                'key' => ['reportDate' => 1, 'fuelType' => 1, 'station._id' => 1],
                'options' => ['unique' => true],
            ],
            'fuelType_postCode' => ['key' => ['fuelType' => 1, 'station.address.postCode' => 1]],
            'postCode' => ['key' => ['station.address.postCode' => 1]],
            'brand' => ['key' => ['station.brand' => 1]],
        ];

        $this->ensureIndexes(
            $this->database->selectCollection('priceReports'),
            $expectedIndexes,
            $output,
        );
    }

    private function createPriceReportView(StyleInterface $output): void
    {
        foreach ($this->database->listCollections() as $collectionInfo) {
            if ($collectionInfo->getName() == 'priceReportsView') {
                // Todo: check if pipeline is correct
                return;
            }
        }

        $output->text('Creating priceReports view...');
        $this->database->createCollection(
            'priceReportsView',
            [
                'pipeline' => $this->getPriceReports->getPipeline(),
                'viewOn' => 'prices',
            ],
        );
        $output->text('Done!');
    }
}
