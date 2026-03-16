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

namespace HyperfTest\Nsq;

use Hyperf\Config\Config;
use Hyperf\Nsq\Nsqd\Api;
use Hyperf\Nsq\Nsqd\Channel;
use Hyperf\Nsq\Nsqd\Client;
use Hyperf\Nsq\Nsqd\Topic;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class NsqdApiTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testTopic()
    {
        $client = new Topic($this->getClient());
        $this->assertTrue($client->create('hyperf.test'));
        $this->assertTrue($client->pause('hyperf.test'));
        $this->assertTrue($client->unpause('hyperf.test'));
        $this->assertTrue($client->empty('hyperf.test'));
        $this->assertTrue($client->delete('hyperf.test'));
    }

    public function testChannel()
    {
        $client = new Topic($this->getClient());
        $client->create('hyperf.test');

        $client = new Channel($this->getClient());
        $this->assertTrue($client->create('hyperf.test', 'test'));
        $this->assertTrue($client->pause('hyperf.test', 'test'));
        $this->assertTrue($client->unpause('hyperf.test', 'test'));
        $this->assertTrue($client->empty('hyperf.test', 'test'));
        $this->assertTrue($client->delete('hyperf.test', 'test'));
    }

    public function testBasicApi()
    {
        $client = new Api($this->getClient());

        // /info
        $info = $client->info();
        $this->assertInstanceOf(ResponseInterface::class, $info);
        $this->assertSame(200, $info->getStatusCode());
        $this->assertJson((string) $info->getBody());

        // /ping
        $ping = $client->ping();
        $this->assertTrue($ping);

        // /stats - text
        $stats = $client->stats();
        $this->assertInstanceOf(ResponseInterface::class, $stats);
        $this->assertSame(200, $stats->getStatusCode());
        $this->assertTrue(is_string((string) $stats->getBody()));

        // /stats - json
        $stats = $client->stats('json');
        $this->assertInstanceOf(ResponseInterface::class, $stats);
        $this->assertSame(200, $stats->getStatusCode());
        $this->assertJson((string) $stats->getBody());
    }

    public function testConfigNsalookupdTcpAddresses()
    {
        $client = new Api($this->getClient());

        //  PUT /config/nsqlookupd_tcp_addresses
        $addresses = $client->setConfigNsqlookupdTcpAddresses(['nsqlookupd:4160', 'nsqlookupd:4161']);
        $this->assertTrue($addresses);
        $this->assertSame('["nsqlookupd:4160","nsqlookupd:4161"]', (string) $client->getConfigNsqlookupdTcpAddresses()->getBody());

        // Reset addresses
        $client->setConfigNsqlookupdTcpAddresses(['nsqlookupd:4160']);

        //  GET /config/nsqlookupd_tcp_addresses
        $addresses = $client->getConfigNsqlookupdTcpAddresses();
        $this->assertInstanceOf(ResponseInterface::class, $addresses);
        $this->assertSame(200, $addresses->getStatusCode());
        $this->assertSame('["nsqlookupd:4160"]', (string) $addresses->getBody());
    }

    protected function getClient()
    {
        $config = new Config([]);
        return new Client($config);
    }
}
