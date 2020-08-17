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

use Hyperf\Contract\ConfigInterface;
use Hyperf\Guzzle\CoroutineHandler;
use Hyperf\Nsq\Nsqd\Client;
use HyperfTest\Nsq\Stub\CoroutineHandlerStub;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class HttpClientTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }

    public function testHttpClientWithEmptyConfig()
    {
        $config = Mockery::mock(ConfigInterface::class);
        $config->shouldReceive('get')->with('nsq', [])->andReturn([]);

        $client = new Client($config);
        $this->assertSame('http://127.0.0.1:4151', $client->getOptions()['base_uri']);
        $this->assertInstanceOf(CoroutineHandler::class, $client->getOptions()['handler']);
    }

    public function testHttpClientWithHost()
    {
        $config = Mockery::mock(ConfigInterface::class);
        $config->shouldReceive('get')->with('nsq', [])->andReturn([
            'host' => '192.168.1.1',
            'nsqd' => [
                'port' => 14151,
            ],
        ]);

        $client = new Client($config);
        $this->assertSame('http://192.168.1.1:14151', $client->getOptions()['base_uri']);
        $this->assertInstanceOf(CoroutineHandler::class, $client->getOptions()['handler']);
    }

    public function testHttpClientWithOptions()
    {
        $config = Mockery::mock(ConfigInterface::class);
        $config->shouldReceive('get')->with('nsq', [])->andReturn([
            'nsqd' => [
                'options' => [
                    'base_uri' => 'https://nsq.hyperf.io',
                    'handler' => new CoroutineHandlerStub(),
                ],
            ],
        ]);

        $client = new Client($config);
        $this->assertSame('https://nsq.hyperf.io', $client->getOptions()['base_uri']);
        $this->assertInstanceOf(CoroutineHandlerStub::class, $client->getOptions()['handler']);
    }
}
