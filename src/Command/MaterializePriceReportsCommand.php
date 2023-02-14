<?php

namespace App\Command;

use App\Pipeline\MaterializePriceReports;
use InvalidArgumentException;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Collection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function array_unshift;
use function sprintf;
use function strtotime;

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

    protected function configure()
    {
        $this
            ->addOption('startDate', null, InputOption::VALUE_REQUIRED, 'Date to start from (inclusive)')
            ->addOption('endDate', null, InputOption::VALUE_REQUIRED, 'Date to end at (exclusive)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $matchExpr = $this->createMatchExpression($input);
        } catch (InvalidArgumentException $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }

        $pipeline = $this->pipeline->getPipeline();
        if ($matchExpr) {
            array_unshift($pipeline, ['$match' => $matchExpr]);
        }

        $io->text('Materialising price reports...');

        $startTime = microtime(true);
        $this->prices->aggregate($pipeline);
        $endTime = microtime(true);

        $io->success(sprintf('Done in %.5f seconds', $endTime - $startTime));

        return Command::SUCCESS;
    }

    private function createMatchExpression(InputInterface $input): array
    {
        $matchExpr = [];
        $startDate = $input->getOption('startDate');
        $endDate = $input->getOption('endDate');

        if ($startDate) {
            $startDate = strtotime($startDate);
            if ($startDate === false) {
                throw new InvalidArgumentException(sprintf('Could not parse start date "%s"', $startDate));
            }

            $matchExpr['reportDate']['$gte'] = new UTCDateTime($startDate * 1000);
        }

        if ($endDate) {
            $endDate = strtotime($endDate);
            if ($endDate === false) {
                throw new InvalidArgumentException(sprintf('Could not parse end date "%s"', $startDate));
            }

            $matchExpr['reportDate']['$lt'] = new UTCDateTime($endDate * 1000);
        }

        return $matchExpr;
    }
}
