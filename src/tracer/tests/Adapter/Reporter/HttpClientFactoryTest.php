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

namespace HyperfTest\Tracer\Adapter\Reporter;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Hyperf\Engine\Channel;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Tracer\Adapter\Reporter\HttpClientFactory;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class HttpClientFactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testContentLengthHeaderIsString(): void
    {
        $requestOptions = [];
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('post')
            ->once()
            ->withArgs(static function (string $url, array $options) use (&$requestOptions): bool {
                $requestOptions = $options;

                return $url === 'https://zipkin.test/api/v2/spans';
            })
            ->andReturn(new Response(202));

        $clientFactory = Mockery::mock(ClientFactory::class);
        $clientFactory->shouldReceive('create')->once()->andReturn($client);

        $factory = new TestableHttpClientFactory($clientFactory);
        $reporter = $factory->build([
            'endpoint_url' => 'https://zipkin.test/api/v2/spans',
        ]);

        $reporter('payload');
        $factory->runQueuedRequest();

        $this->assertSame('7', $requestOptions['headers']['Content-Length']);
    }
}

final class TestableHttpClientFactory extends HttpClientFactory
{
    private TestChannel $testChannel;

    public function __construct(ClientFactory $clientFactory)
    {
        parent::__construct($clientFactory);
        $this->testChannel = new TestChannel();
    }

    public function runQueuedRequest(): void
    {
        ($this->testChannel->closure)();
    }

    protected function loop(): void
    {
        $this->chan = $this->testChannel;
    }
}

final class TestChannel extends Channel
{
    public ?Closure $closure = null;

    public function __construct()
    {
    }

    public function push(mixed $data, float $timeout = -1): bool
    {
        $this->closure = $data;

        return true;
    }

    public function isClosing(): bool
    {
        return false;
    }
}
