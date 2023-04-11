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
namespace HyperfTest\Consul;

use Exception;
use GuzzleHttp\Client;
use Hyperf\Consul\ConsulResponse;
use Hyperf\Consul\KV;
use Hyperf\Consul\KVInterface;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Container;
use Hyperf\Guzzle\ClientFactory;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * @internal
 * @covers \Hyperf\Consul\KV
 */
class KVTest extends TestCase
{
    private $kv;

    protected function setUp(): void
    {
        $this->kv = $this->createKV();
        $this->kv->delete('test', ['recurse' => true]);
    }

    public function testSetGetWithDefaultOptions()
    {
        $value = date('r');
        $this->kv->put('test/my/key', $value);

        $response = $this->kv->get('test/my/key');
        $this->assertInstanceOf(ConsulResponse::class, $response);

        $json = $response->json();
        $this->assertSame("\"{$value}\"", base64_decode($json[0]['Value']));
    }

    public function testSetGetWithRawOption()
    {
        $value = date('r');
        $this->kv->put('test/my/key', $value);

        $response = $this->kv->get('test/my/key', ['raw' => true]);
        $this->assertInstanceOf(ConsulResponse::class, $response);

        $body = (string) $response->getBody();
        $this->assertSame("\"{$value}\"", $body);
    }

    public function testSetGetWithFlagsOption()
    {
        $flags = mt_rand();
        $this->kv->put('test/my/key', 'hello', ['flags' => $flags]);

        $response = $this->kv->get('test/my/key');
        $this->assertInstanceOf(ConsulResponse::class, $response);

        $json = $response->json();
        $this->assertSame($flags, $json[0]['Flags']);
    }

    public function testSetGetWithKeysOption()
    {
        $this->kv->put('test/my/key1', 'hello 1');
        $this->kv->put('test/my/key2', 'hello 2');
        $this->kv->put('test/my/key3', 'hello 3');

        $response = $this->kv->get('test/my', ['keys' => true]);
        $this->assertInstanceOf(ConsulResponse::class, $response);

        $json = $response->json();
        $this->assertSame(['test/my/key1', 'test/my/key2', 'test/my/key3'], $json);
    }

    public function testDeleteWithDefaultOptions()
    {
        $this->kv->put('test/my/key', 'hello');
        $this->kv->get('test/my/key');
        $this->kv->delete('test/my/key');

        try {
            $this->kv->get('test/my/key');
            $this->fail('fail because the key does not exist anymore.');
        } catch (Exception $e) {
            $this->assertInstanceOf('Hyperf\Consul\Exception\ServerException', $e);
            $this->assertStringContainsString('404 Not Found', $e->getMessage());
        }
    }

    public function testDeleteWithRecurseOption()
    {
        $this->kv->put('test/my/key1', 'hello 1');
        $this->kv->put('test/my/key2', 'hello 2');
        $this->kv->put('test/my/key3', 'hello 3');

        $this->kv->get('test/my/key1');
        $this->kv->get('test/my/key2');
        $this->kv->get('test/my/key3');

        $this->kv->delete('test/my', ['recurse' => true]);

        for ($i = 1; $i < 3; ++$i) {
            try {
                $this->kv->get('test/my/key' . $i);
                $this->fail('fail because the key does not exist anymore.');
            } catch (Exception $e) {
                $this->assertInstanceOf('Hyperf\Consul\Exception\ServerException', $e);
                $this->assertStringContainsString('404 Not Found', $e->getMessage());
            }
        }
    }

    private function createKV(): KVInterface
    {
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturn(new NullLogger());
        $container->shouldReceive('get')->with(ClientFactory::class)->andReturn(new ClientFactory($container));
        $container->shouldReceive('make')->andReturnUsing(function ($name, $options) {
            if ($name === Client::class) {
                return new Client($options['config']);
            }
        });
        ApplicationContext::setContainer($container);
        return new KV(function () use ($container) {
            return $container->get(ClientFactory::class)->create([
                'base_uri' => KV::DEFAULT_URI,
            ]);
        }, $container->get(StdoutLoggerInterface::class));
    }
}
