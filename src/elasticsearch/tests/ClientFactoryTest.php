<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Elasticsearch;

use Elasticsearch\ClientBuilder;
use Hyperf\Elasticsearch\ClientFactory;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class ClientFactoryTest extends TestCase
{
    public function testClientFactoryCreate()
    {
        $container = Mockery::mock(ContainerInterface::class);

        $clientFactory = new ClientFactory($container);

        $client = $clientFactory->builder();

        $this->assertInstanceOf(ClientBuilder::class, $client);
    }
}
