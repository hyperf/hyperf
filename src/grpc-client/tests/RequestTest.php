<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
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
        $this->assertSame([
            'content-type' => 'application/grpc+proto',
            'user-agent' => 'grpc-php-hyperf/1.0 (hyperf-grpc-client/dev-master)',
        ], $request->headers);
        $this->assertSame($path, $request->path);
        $this->assertSame(Parser::serializeMessage($info), $request->data);
    }

    public function testGetDefaultHeaders()
    {
        $request = new Request($path = 'grpc.service/path', $info = new Info());
        $this->assertSame([
            'content-type' => 'application/grpc+proto',
            'user-agent' => 'grpc-php-hyperf/1.0 (hyperf-grpc-client/dev-master)',
        ], $request->getDefaultHeaders());
    }

    public function testUserDefinedHeaders()
    {
        $request = new Request($path = 'grpc.service/path', $info = new Info(), [
            'content-type' => 'application/grpc',
            'foo' => 'bar',
        ]);
        $this->assertSame([
            'content-type' => 'application/grpc',
            'user-agent' => 'grpc-php-hyperf/1.0 (hyperf-grpc-client/dev-master)',
            'foo' => 'bar',
        ], $request->headers);
    }
}
