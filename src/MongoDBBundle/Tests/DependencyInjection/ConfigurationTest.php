<?php

namespace MongoDB\Bundle\Tests\DependencyInjection;

use MongoDB\Bundle\DependencyInjection\Configuration;
use MongoDB\Driver\ServerApi;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

/** @covers \MongoDB\Bundle\DependencyInjection\Configuration */
final class ConfigurationTest extends TestCase
{
    public function testProcess(): void
    {
        $configs = [[
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
        ]];

        $config = $this->process($configs);

        $this->assertArrayHasKey('clients', $config);
        $clients = $config['clients'];

        $this->assertCount(2, $clients);

        $this->assertArrayHasKey('default', $clients);
        $this->assertSame('mongodb://localhost:27017', $clients['default']['uri']);
        $this->assertEquals(['readPreference' => 'primary'], $clients['default']['uriOptions']);
        $this->assertEquals([], $clients['default']['driverOptions']);

        $this->assertArrayHasKey('secondary', $clients);
        $this->assertSame('mongodb://localhost:27018', $clients['secondary']['uri']);
        $this->assertEquals([], $clients['secondary']['uriOptions']);
        $this->assertEquals(['serverApi' => new ServerApi(ServerApi::V1)], $clients['secondary']['driverOptions']);
    }

    private function process(array $configs): array
    {
        $processor = new Processor();

        return $processor->processConfiguration(new Configuration(), $configs);
    }
}
