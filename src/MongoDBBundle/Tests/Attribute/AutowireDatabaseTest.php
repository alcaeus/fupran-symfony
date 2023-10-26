<?php

namespace MongoDB\Bundle\Tests\Attribute;

use MongoDB\Bundle\Attribute\AutowireDatabase;
use MongoDB\Database;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Reference;

/** @covers \MongoDB\Bundle\Attribute\AutowireDatabase */
final class AutowireDatabaseTest extends TestCase
{
    public function testCollection(): void
    {
        $autowire = new AutowireDatabase(
            clientId: 'default',
            databaseName: 'mydb',
            options: ['foo' => 'bar'],
        );

        $this->assertEquals([new Reference('mongodb.client.default'), 'selectDatabase'], $autowire->value);

        $definition = $autowire->buildDefinition(
            value: $autowire->value,
            type: Database::class,
            parameter: new \ReflectionParameter(
                function (Database $db) {},
                'db',
            ),
        );

        $this->assertSame(Database::class, $definition->getClass());
        $this->assertEquals($autowire->value, $definition->getFactory());
        $this->assertSame('mydb', $definition->getArgument(0));
        $this->assertEquals(['foo' => 'bar'], $definition->getArgument(1));
    }
}
