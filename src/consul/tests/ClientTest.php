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

use HyperfTest\Consul\Stub\Client;
use Mockery;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * @internal
 * @covers \Hyperf\Consul\Client
 */
class ClientTest extends TestCase
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var ReflectionMethod
     */
    private $method;

    protected function setUp(): void
    {
        $this->client = new Client(function () {
            return Mockery::mock(\GuzzleHttp\Client::class);
        });
        $reflectionClass = new ReflectionClass(Client::class);
        $method = $reflectionClass->getMethod('resolveOptions');
        $method->setAccessible(true);
        $this->method = $method;
    }

    public function testResolveOptions()
    {
        $options = [
            'foo' => 'bar',
            'hello' => 'world',
            'baz' => 'inga',
        ];

        $availableOptions = [
            'foo', 'baz',
        ];

        $result = $this->method->invoke($this->client, $options, $availableOptions);

        $expected = [
            'foo' => 'bar',
            'baz' => 'inga',
        ];

        $this->assertSame($expected, $result);
    }

    public function testResolveOptionsWithoutMatchingOptions()
    {
        $options = [
            'hello' => 'world',
        ];

        $availableOptions = [
            'foo', 'baz',
        ];

        $result = $this->method->invoke($this->client, $options, $availableOptions);

        $this->assertSame([], $result);
    }
}
