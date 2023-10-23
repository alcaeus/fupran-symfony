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
    }
}
