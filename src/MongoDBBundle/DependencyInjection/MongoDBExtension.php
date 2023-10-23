<?php

namespace MongoDB\Bundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

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
        $prototypeDefinition = $container->getDefinition('mongodb.prototype.client');

        foreach ($clients as $client => $clientConfiguration) {
            $clientDefinition = clone $prototypeDefinition;
            $clientDefinition->setArgument('$uri', $clientConfiguration['uri']);
            $clientDefinition->setArgument('$uriOptions', $clientConfiguration['uriOptions']);
            $clientDefinition->setArgument('$driverOptions', $clientConfiguration['driverOptions']);

            $container->setDefinition('mongodb.client.' . $client, $clientDefinition);
        }

        // Remove the prototype definition as it's tagged as client
        $container->removeDefinition('mongodb.prototype.client');
    }
}
