<?php

namespace MongoDB\Bundle\Tests\Attribute;

use Exception;
use MongoDB\Bundle\Attribute\AutowireCollection;
use MongoDB\Collection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Reference;

/** @covers \MongoDB\Bundle\Attribute\AutowireCollection */
final class AutowireCollectionTest extends TestCase
{
    public function testConstructWithDocumentClassAndCodec(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot combine codec option and documentClass');

        new AutowireCollection(
            clientId: 'default',
            databaseName: 'test',
            collectionName: 'test',
            documentClass: self::class,
            options: ['codec' => 'test'],
        );
    }

    public function testCollection(): void
    {
        $autowire = new AutowireCollection(
            clientId: 'default',
            databaseName: 'mydb',
            collectionName: 'test',
            options: ['foo' => 'bar'],
        );

        $this->assertEquals([new Reference('mongodb.client.default'), 'selectCollection'], $autowire->value);

        $definition = $autowire->buildDefinition(
            value: $autowire->value,
            type: Collection::class,
            parameter: new \ReflectionParameter(
                function (Collection $collection) {},
                'collection',
            ),
        );

        $this->assertSame(Collection::class, $definition->getClass());
        $this->assertEquals($autowire->value, $definition->getFactory());
        $this->assertSame('mydb', $definition->getArgument(0));
        $this->assertSame('test', $definition->getArgument(1));
        $this->assertEquals(['foo' => 'bar'], $definition->getArgument(2));
    }

    public function testWithoutCollection(): void
    {
        $autowire = new AutowireCollection(
            clientId: 'default',
            databaseName: 'mydb',
            options: ['foo' => 'bar'],
        );

        $this->assertEquals([new Reference('mongodb.client.default'), 'selectCollection'], $autowire->value);

        $definition = $autowire->buildDefinition(
            value: $autowire->value,
            type: Collection::class,
            parameter: new \ReflectionParameter(
                function (Collection $collection) {},
                'priceReports',
            ),
        );

        $this->assertSame(Collection::class, $definition->getClass());
        $this->assertEquals($autowire->value, $definition->getFactory());
        $this->assertSame('priceReports', $definition->getArgument(0));
        $this->assertSame('test', $definition->getArgument(1));
        $this->assertEquals(['foo' => 'bar'], $definition->getArgument(2));
    }
}
