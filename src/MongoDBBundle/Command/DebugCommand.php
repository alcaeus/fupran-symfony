<?php

namespace MongoDB\Bundle\Command;

use MongoDB\Client;
use ReflectionExtension;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ServiceLocator;
use function array_slice;
use function explode;
use function extension_loaded;
use function ob_start;

#[AsCommand(
    name: 'mongodb:debug',
    description: 'Shows debug information about the MongoDB integration',
)]
class DebugCommand extends Command
{
    public function __construct(
        private ServiceLocator $clients,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->printExtensionInformation($io);
        $this->printClientInformation($io);

        return 0;
    }

    private function printExtensionInformation(SymfonyStyle $io): void
    {
        $io->section('MongoDB Extension Information');

        if (! extension_loaded('mongodb')) {
            $io->error('The MongoDB extension is not loaded.');
            // TODO: Add helpful information on how to solve this

            return;
        }

        $extension = new ReflectionExtension('mongodb');

        ob_start();
        $extension->info();
        $info = explode("\n", ob_get_clean());

        $io->text(array_slice($info, 3));
    }

    private function printClientInformation(SymfonyStyle $io)
    {
        $io->section('MongoDB Client Information');
        $io->text(sprintf('%d clients configured', $this->clients->count()));

        $table = $io->createTable();
        $table->setHeaders(['Service ID', 'URI']);

        foreach ($this->clients->getProvidedServices() as $serviceId => $class) {
            $client = $this->clients->get($serviceId);
            $table->addRow([$serviceId, $client->__debugInfo()['uri']]);
        }

        $table->render();
    }
}
