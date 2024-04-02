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

namespace HyerfTest\Guzzle\Cases;

use Hyperf\Guzzle\RingPHP\CoroutineHandler;
use HyperfTest\Guzzle\Stub\RingPHPCoroutineHanderStub;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class RingPHPCoroutineHandlerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testUserInfo()
    {
        $handler = new RingPHPCoroutineHanderStub();

        $res = $handler([
            'http_method' => 'GET',
            'headers' => ['host' => ['127.0.0.1:8080']],
            'uri' => '/echo',
            'client' => [
                'curl' => [
                    CURLOPT_USERPWD => 'username:password',
                ],
            ],
        ]);

        $json = json_decode(stream_get_contents($res['body']), true);

        $this->assertEquals('Basic ' . base64_encode('username:password'), $json['headers']['Authorization']);
    }

    public function testCreatesErrors()
    {
        $handler = new CoroutineHandler();
        $response = $handler([
            'http_method' => 'GET',
            'uri' => '/',
            'headers' => ['host' => ['127.0.0.1:8080']],
            'client' => ['timeout' => 0.001],
        ]);

        $this->assertNull($response['status']);
        $this->assertNull($response['reason']);
        $this->assertEquals([], $response['headers']);
        $this->assertInstanceOf(
            'GuzzleHttp\Ring\Exception\RingException',
            $response['error']
        );

        $this->assertEquals(
            0,
            strpos('Connection timed out errCode=', $response['error']->getMessage())
        );
    }

    public function testWithoutQuery()
    {
        $handler = new RingPHPCoroutineHanderStub();

        $res = $handler([
            'http_method' => 'GET',
            'headers' => ['host' => ['127.0.0.1:8080']],
            'uri' => '/echo?a=1&b=2',
        ]);

        $json = json_decode(stream_get_contents($res['body']), true);

        $this->assertEquals('/echo?a=1&b=2', $json['uri']);
    }
}
