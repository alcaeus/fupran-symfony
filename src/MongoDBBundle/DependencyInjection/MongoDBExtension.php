<?php

namespace MongoDB\Bundle\DependencyInjection;

use MongoDB\Collection;
use MongoDB\Database;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

use function sprintf;

class MongoDBExtension extends Extension
{
    public function getAlias(): string
    {
        return 'mongodb';
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(dirname(__DIR__) . '/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->createClients($config['clients'], $container);
    }

    private function createClients(array $clients, ContainerBuilder $container): void
    {
        $clientPrototype = $container->getDefinition('mongodb.prototype.client');

        foreach ($clients as $clientId => $clientConfiguration) {
            $clientServiceName = sprintf('mongodb.client.%s', $clientId);

            $clientDefinition = clone $clientPrototype;
            $clientDefinition->setArgument('$uri', $clientConfiguration['uri']);
            $clientDefinition->setArgument('$uriOptions', $clientConfiguration['uriOptions'] ?? []);
            $clientDefinition->setArgument('$driverOptions', $clientConfiguration['driverOptions'] ?? []);

            $container->setDefinition($clientServiceName, $clientDefinition);

            foreach ($clientConfiguration['databases'] as $databaseId => $databaseConfiguration) {
                $databaseName = $databaseConfiguration['name'] ?? $databaseId;
                $databaseServiceName = sprintf('mongodb.database.%s.%s', $clientId, $databaseId);

                $databaseDefinition = new Definition(Database::class);
                $databaseDefinition->setFactory([$clientDefinition, 'selectDatabase']);
                $databaseDefinition->setArgument('$databaseName', $databaseName);
                $databaseDefinition->setArgument('$options', $databaseConfiguration['options'] ?? []);
                $databaseDefinition->addTag('mongodb.database', ['clientName' => $clientId]);

                $container->setDefinition($databaseServiceName, $databaseDefinition);

                foreach ($databaseConfiguration['collections'] as $collectionName => $collectionConfiguration) {
                    $collectionServiceName = sprintf('mongodb.collection.%s.%s.%s', $clientId, $databaseId, $collectionName);

                    $collectionDefinition = new Definition(Collection::class);
                    $collectionDefinition->setFactory([$databaseDefinition, 'selectCollection']);
                    $collectionDefinition->setArgument('$collectionName', $collectionName);
                    $collectionDefinition->setArgument('$options', $collectionConfiguration['options'] ?? []);
                    $collectionDefinition->addTag('mongodb.collection', ['clientName' => $clientId, 'databaseName' => $databaseName]);

                    $container->setDefinition($collectionServiceName, $collectionDefinition);
                }
            }
        }

        // Remove the prototype definition as it's tagged as client
        $container->removeDefinition('mongodb.prototype.client');
    }
}
