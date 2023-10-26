<?php

namespace MongoDB\Bundle\Tests\DependencyInjection;

use MongoDB\Bundle\DependencyInjection\MongoDBExtension;
use MongoDB\Client;
use MongoDB\Driver\ServerApi;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\EnvPlaceholderParameterBag;

/** @covers \MongoDB\Bundle\DependencyInjection\MongoDBExtension */
final class MongoDBExtensionTest extends TestCase
{
    public function testLoadWithSingleClient(): void
    {
        $container = $this->getContainer(
            [[
                'clients' => [
                    ['id' => 'default', 'uri' => 'mongodb://localhost:27017'],
                ],
            ]],
        );

        $this->assertTrue($container->hasDefinition('mongodb.client.default'));
        $this->assertTrue($container->hasAlias(Client::class));

        // Check service definition
        $definition = $container->getDefinition('mongodb.client.default');
        $this->assertSame(Client::class, $definition->getClass());
        $this->assertSame('mongodb://localhost:27017', $definition->getArgument('$uri'));

        // Check alias definition
        $alias = $container->getAlias(Client::class);
        $this->assertSame('mongodb.client.default', (string) $alias);
    }

    public function testLoadWithMultipleClients(): void
    {
        $container = $this->getContainer(
            [[
                'clients' => [
                    [
                        'id' => 'default',
                        'uri' => 'mongodb://localhost:27017',
                        'uriOptions' => ['readPreference' => 'primary'],
                    ],
                    [
                        'id' => 'secondary',
                        'uri' => 'mongodb://localhost:27018',
                        'driverOptions' => ['serverApi' => new ServerApi(ServerApi::V1)],
                    ],
                ],
            ]],
        );

        $this->assertTrue($container->hasDefinition('mongodb.client.default'));
        $this->assertTrue($container->hasDefinition('mongodb.client.secondary'));
        $this->assertFalse($container->hasAlias(Client::class));

        // Check service definitions
        $definition = $container->getDefinition('mongodb.client.default');
        $this->assertSame(Client::class, $definition->getClass());
        $this->assertSame('mongodb://localhost:27017', $definition->getArgument('$uri'));
        $this->assertEquals(['readPreference' => 'primary'], $definition->getArgument('$uriOptions'));

        $definition = $container->getDefinition('mongodb.client.secondary');
        $this->assertSame(Client::class, $definition->getClass());
        $this->assertSame('mongodb://localhost:27018', $definition->getArgument('$uri'));
        $this->assertEquals(['serverApi' => new ServerApi(ServerApi::V1)], $definition->getArgument('$driverOptions'));
    }

    private function getContainer(array $config = [], array $thirdPartyDefinitions = []): ContainerBuilder
    {
        $container = new ContainerBuilder(new EnvPlaceholderParameterBag());
        foreach ($thirdPartyDefinitions as $id => $definition) {
            $container->setDefinition($id, $definition);
        }

        $container->getCompilerPassConfig()->setOptimizationPasses([]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);

        $loader = new MongoDBExtension();
        $loader->load($config, $container);
        $container->compile();

        return $container;
    }
}
