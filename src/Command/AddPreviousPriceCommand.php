<?php

namespace App\Command;

use App\Aggregation\AddPreviousPrice;
use MongoDB\Builder\BuilderEncoder;
use MongoDB\Bundle\Attribute\AutowireCollection;
use MongoDB\Collection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function microtime;
use function sprintf;

#[AsCommand(
    name: 'app:add-previous-price',
    description: 'Denormalises station information into price reports',
)]
class AddPreviousPriceCommand extends Command
{
    public function __construct(
        #[AutowireCollection]
        private readonly Collection $priceReports,
        private readonly AddPreviousPrice $pipeline,
        private readonly BuilderEncoder $encoder,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->text('Adding previous price data');

        $startTime = microtime(true);
        $this->priceReports->aggregate($this->encoder->encode($this->pipeline->getPipeline()));
        $endTime = microtime(true);

        $io->success(sprintf('Done in %.5f seconds', $endTime - $startTime));

        return Command::SUCCESS;
    }
}
