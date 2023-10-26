<?php

namespace MongoDB\Bundle\DependencyInjection;

use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

use function array_key_first;
use function sprintf;

class MongoDBExtension extends Extension
{
    public function getAlias(): string
    {
        return 'mongodb';
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->createClients($config['clients'], $container);
    }

    /** @internal */
    public static function getClientServiceName(string $clientId): string
    {
        return sprintf('mongodb.client.%s', $clientId);
    }

    private function createClients(array $clients, ContainerBuilder $container): void
    {
        $clientPrototype = $container->getDefinition('mongodb.prototype.client');

        foreach ($clients as $clientId => $clientConfiguration) {
            $clientDefinition = clone $clientPrototype;
            $clientDefinition->setArgument('$uri', $clientConfiguration['uri']);
            $clientDefinition->setArgument('$uriOptions', $clientConfiguration['uriOptions'] ?? []);
            $clientDefinition->setArgument('$driverOptions', $clientConfiguration['driverOptions'] ?? []);

            $container->setDefinition(self::getClientServiceName($clientId), $clientDefinition);
        }

        // Register an autowiring alias if there is only one client
        if (count($clients) === 1) {
            $clientId = array_key_first($clients);

            $container->setAlias(Client::class, self::getClientServiceName($clientId));
        }

        // Remove the prototype definition as it's tagged as client
        $container->removeDefinition('mongodb.prototype.client');
    }
}
