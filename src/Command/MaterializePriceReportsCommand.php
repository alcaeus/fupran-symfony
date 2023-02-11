<?php

namespace App\Command;

use App\Pipeline\MaterializePriceReports;
use MongoDB\Collection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:materialize-price-reports',
    description: 'Store price reports in a materialised view',
)]
class MaterializePriceReportsCommand extends Command
{
    public function __construct(
        private readonly Collection $prices,
        private readonly MaterializePriceReports $pipeline,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->text('Materialising price reports...');

        $startTime = microtime(true);
        $this->prices->aggregate($this->pipeline->getPipeline());
        $endTime = microtime(true);

        $io->success(sprintf('Done in %.5f seconds', $endTime - $startTime));

        return Command::SUCCESS;
    }
}
