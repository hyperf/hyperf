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

namespace HyperfTest\ConfigNacos;

use Hyperf\ConfigNacos\Client;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ClientTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testClientPull()
    {
        $container = ContainerStub::getContainer();

        $client = new Client($container);

        $data = $client->pull();

        $this->assertSame(['nacos_config' => ['id' => 1], 'nacos_config.data' => 'Hello World', ['ids' => [1, 2, 3]]], $data);
    }

    public function testClientGetValidNodes()
    {
        $container = ContainerStub::getContainer();

        $client = new Client($container);

        $data = $client->getValidNodes('foo');

        $this->assertSame(1, count($data));
    }
}
