<?php

namespace MongoDB\Bundle\Tests\Attribute;

use MongoDB\Bundle\Attribute\AutowireClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Reference;

/** @covers \MongoDB\Bundle\Attribute\AutowireClient */
final class AutowireClientTest extends TestCase
{
    public function testCollection(): void
    {
        $autowire = new AutowireClient('default');

        $this->assertEquals(new Reference('mongodb.client.default'), $autowire->value);
    }
}
