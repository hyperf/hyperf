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

use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use Hyperf\Elasticsearch\ClientBuilderFactory;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ClientFactoryTest extends TestCase
{
    public function testClientBuilderFactoryCreate()
    {
        $clientFactory = new ClientBuilderFactory();

        $client = $clientFactory->create();

        if (class_exists('Elasticsearch\ClientBuilder')) {
            $this->assertInstanceOf(ClientBuilder::class, $client);
        }
        if (class_exists('Elastic\Elasticsearch\ClientBuilder')) {
            $this->assertInstanceOf(\Elastic\Elasticsearch\ClientBuilder::class, $client);
        }
    }

    public function testHostNotReached()
    {
        if (class_exists('Elasticsearch\Common\Exceptions\NoNodesAvailableException')) {
            $this->expectException(NoNodesAvailableException::class);
        }
        if (class_exists('Elastic\Elasticsearch\Common\Exceptions\NoNodesAvailableException')) {
            $this->expectException(\Elastic\Elasticsearch\Common\Exceptions\NoNodesAvailableException::class);
        }

        $clientFactory = new ClientBuilderFactory();

        $client = $clientFactory->create()->setHosts(['http://127.0.0.1:9201'])->build();

        $client->info();
    }
}
