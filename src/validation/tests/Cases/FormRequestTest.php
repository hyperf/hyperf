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

use Hyperf\HttpMessage\Upload\UploadedFile;
use Hyperf\Utils\Context;
use HyperfTest\Validation\Cases\Stub\DemoRequest;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal
 * @coversNothing
 */
class FormRequestTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
        Context::set(ServerRequestInterface::class, null);
        Context::set('http.request.parsedData', null);
    }

    public function testRequestValidationData()
    {
        $psrRequest = Mockery::mock(ServerRequestInterface::class);
        $file = new UploadedFile('/tmp/tmp_name', 32, 0);
        $psrRequest->shouldReceive('getUploadedFiles')->andReturn([
            'file' => $file,
        ]);
        $psrRequest->shouldReceive('getParsedBody')->andReturn([
            'id' => 1,
        ]);
        $psrRequest->shouldReceive('getQueryParams')->andReturn([]);

        Context::set(ServerRequestInterface::class, $psrRequest);
        $request = new DemoRequest(Mockery::mock(ContainerInterface::class));

        $this->assertEquals(['id' => 1, 'file' => $file], $request->getValidationData());
    }

    public function testRequestValidationDataWithSameKey()
    {
        $psrRequest = Mockery::mock(ServerRequestInterface::class);
        $file = new UploadedFile('/tmp/tmp_name', 32, 0);
        $psrRequest->shouldReceive('getUploadedFiles')->andReturn([
            'file' => [$file],
        ]);
        $psrRequest->shouldReceive('getParsedBody')->andReturn([
            'file' => ['Invalid File.'],
        ]);
        $psrRequest->shouldReceive('getQueryParams')->andReturn([]);

        Context::set(ServerRequestInterface::class, $psrRequest);
        $request = new DemoRequest(Mockery::mock(ContainerInterface::class));

        $this->assertEquals(['file' => ['Invalid File.', $file]], $request->getValidationData());
    }
}
