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
namespace HyperfTest\GrpcClient;

use Grpc\Info;
use Hyperf\Grpc\Parser;
use Hyperf\GrpcClient\Request;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class RequestTest extends TestCase
{
    public function testRequest()
    {
        $request = new Request($path = 'grpc.service/path', $info = new Info());
        $this->assertSame(3, count($request->headers));
        $this->assertSame('application/grpc+proto', $request->headers['content-type']);
        $this->assertRegExp('/^grpc-php-hyperf\/1.0 \(hyperf-grpc-client\/.*\)$/', $request->headers['user-agent']);
        $this->assertSame($path, $request->path);
        $this->assertSame(Parser::serializeMessage($info), $request->data);
    }

    public function testGetDefaultHeaders()
    {
        $request = new Request($path = 'grpc.service/path', $info = new Info());
        $this->assertSame(3, count($request->getDefaultHeaders()));
        $this->assertSame('application/grpc+proto', $request->getDefaultHeaders()['content-type']);
        $this->assertRegExp('/^grpc-php-hyperf\/1.0 \(hyperf-grpc-client\/.*\)$/', $request->getDefaultHeaders()['user-agent']);
    }

    public function testUserDefinedHeaders()
    {
        $request = new Request($path = 'grpc.service/path', $info = new Info(), [
            'content-type' => 'application/grpc',
            'foo' => 'bar',
        ]);

        $this->assertSame(4, count($request->headers));
        $this->assertSame('application/grpc', $request->headers['content-type']);
        $this->assertRegExp('/^grpc-php-hyperf\/1.0 \(hyperf-grpc-client\/.*\)$/', $request->headers['user-agent']);
        $this->assertSame('bar', $request->headers['foo']);
    }
}
