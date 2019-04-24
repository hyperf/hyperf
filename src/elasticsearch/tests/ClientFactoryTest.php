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
use Hyperf\Elasticsearch\ClientBuilderFactory;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class ClientFactoryTest extends TestCase
{
    public function testClientBuilderFactoryCreate()
    {
        $container = Mockery::mock(ContainerInterface::class);

        $clientFactory = new ClientBuilderFactory($container);

        $client = $clientFactory->create();

        $this->assertInstanceOf(ClientBuilder::class, $client);
    }
}
