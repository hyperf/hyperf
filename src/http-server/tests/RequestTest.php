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

namespace HyperfTest\HttpServer;

use Hyperf\HttpMessage\Upload\UploadedFile;
use Hyperf\HttpServer\Request;
use Hyperf\Utils\Context;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal
 * @coversNothing
 */
class RequestTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
        Context::set(ServerRequestInterface::class, null);
    }

    public function testRequestHasFile()
    {
        $psrRequest = Mockery::mock(ServerRequestInterface::class);
        $psrRequest->shouldReceive('getUploadedFiles')->andReturn([
            'file' => new UploadedFile('tmp_name', 32, 0),
        ]);
        Context::set(ServerRequestInterface::class, $psrRequest);
        $request = new Request();

        $this->assertTrue($request->hasFile('file'));
        $this->assertFalse($request->hasFile('file2'));
        $this->assertInstanceOf(UploadedFile::class, $request->file('file'));
    }
}
