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

namespace HyperfTest\Validation\Cases;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Container;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Validation\NotPwnedVerifier;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 * @coversNothing
 */
class ValidationNotPwnedVerifierTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testEmptyValues(): void
    {
        $httpFactory = m::mock(ClientFactory::class);
        $client = m::mock(Client::class);
        $httpFactory->allows('create')->with([
            'timeout' => 30,
        ])->andReturn($client);
        $verifier = new NotPwnedVerifier($httpFactory);

        foreach (['', false, 0] as $password) {
            $this->assertFalse($verifier->verify([
                'value' => $password,
                'threshold' => 0,
            ]));
        }
    }

    public function testApiResponseGoesWrong(): void
    {
        $response = m::mock(Response::class);

        $httpFactory = m::mock(ClientFactory::class);
        $client = m::mock(Client::class);
        $httpFactory->allows('create')->with([
            'timeout' => 30,
        ])->andReturn($client);
        $client->allows('get')->andReturn($response);
        $response->allows('getStatusCode')
            ->once()
            ->andReturn(200);
        $response->allows('getBody')->andReturn(Utils::streamFor(''));

        $verifier = new NotPwnedVerifier($httpFactory);

        $this->assertTrue($verifier->verify([
            'value' => 123123123,
            'threshold' => 0,
        ]));
    }

    public function testApiGoesDown(): void
    {
        $httpFactory = m::mock(ClientFactory::class);
        $client = m::mock(Client::class);
        $httpFactory->allows('create')->with([
            'timeout' => 30,
        ])->andReturn($client);
        $response = m::mock(Response::class);
        $client->allows('get')
            ->once()
            ->with(
                'https://api.pwnedpasswords.com/range/88EA3',
                [
                    'headers' => [
                        'Add-Padding' => true,
                    ],
                ]
            )->andThrow($response);

        $response->allows('getStatusCode')->andReturn(500);

        $verifier = new NotPwnedVerifier($httpFactory);

        $this->assertTrue($verifier->verify([
            'value' => 123123123,
            'threshold' => 0,
        ]));
    }

    public function testDnsDown()
    {
        $container = m::mock(Container::class);
        ApplicationContext::setContainer($container);
        $exception = new RuntimeException();

        $httpFactory = m::mock(ClientFactory::class);
        $client = m::mock(Client::class);
        $httpFactory->allows('create')->with([
            'timeout' => 30,
        ])->andReturn($client);
        $client->allows('get')->once()->andThrow($exception);
        $this->expectException(RuntimeException::class);
        $verifier = new NotPwnedVerifier($httpFactory);
        $this->assertTrue($verifier->verify([
            'value' => 123123123,
            'threshold' => 0,
        ]));
    }
}
