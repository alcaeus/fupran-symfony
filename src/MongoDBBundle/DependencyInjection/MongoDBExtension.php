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

        // Remove the prototype definition as it's tagged as client
        $container->removeDefinition('mongodb.prototype.client');
    }
}
