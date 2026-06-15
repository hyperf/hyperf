<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Elasticsearch;

use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Hyperf\Elasticsearch\ClientBuilderFactory;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ClientFactoryTest extends TestCase
{
    public function testClientBuilderFactoryCreate()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->andReturn(false);
        $clientFactory = new ClientBuilderFactory($container);

        $client = $clientFactory->create();

        $this->assertInstanceOf(ClientBuilder::class, $client);
    }

    public function testHostNotReached()
    {
        $this->expectException(NoNodeAvailableException::class);

        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->andReturn(false);
        $clientFactory = new ClientBuilderFactory($container);

        $client = $clientFactory->create()->setHosts(['http://127.0.0.1:9201'])->build();

        $client->info();
    }
}
