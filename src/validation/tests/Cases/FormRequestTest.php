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

use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Context\RequestContext;
use Hyperf\Context\ResponseContext;
use Hyperf\Coroutine\Waiter;
use Hyperf\HttpMessage\Server\Response;
use Hyperf\HttpMessage\Upload\UploadedFile;
use Hyperf\Translation\ArrayLoader;
use Hyperf\Translation\Translator;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException;
use Hyperf\Validation\ValidatorFactory;
use HyperfTest\Validation\Cases\Stub\BarSceneRequest;
use HyperfTest\Validation\Cases\Stub\DemoRequest;
use HyperfTest\Validation\Cases\Stub\FooSceneRequest;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swow\Psr7\Message\ServerRequestPlusInterface;
use Throwable;

use function Hyperf\Coroutine\wait;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class FormRequestTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        Context::set(ServerRequestInterface::class, null);
        Context::set('http.request.parsedData', null);
    }

    public function testRequestValidationData()
    {
        $psrRequest = Mockery::mock(ServerRequestPlusInterface::class);
        $file = new UploadedFile('/tmp/tmp_name', 32, 0);
        $psrRequest->shouldReceive('getUploadedFiles')->andReturn([
            'file' => $file,
        ]);
        $psrRequest->shouldReceive('getParsedBody')->andReturn([
            'id' => 1,
        ]);
        $psrRequest->shouldReceive('getQueryParams')->andReturn([]);

        RequestContext::set($psrRequest);
        $request = new DemoRequest(Mockery::mock(ContainerInterface::class));

        $this->assertEquals(['id' => 1, 'file' => $file], $request->getValidationData());
    }

    public function testRequestValidationDataWithSameKey()
    {
        $psrRequest = Mockery::mock(ServerRequestPlusInterface::class);
        $file = new UploadedFile('/tmp/tmp_name', 32, 0);
        $psrRequest->shouldReceive('getUploadedFiles')->andReturn([
            'file' => [$file],
        ]);
        $psrRequest->shouldReceive('getParsedBody')->andReturn([
            'file' => ['Invalid File.'],
        ]);
        $psrRequest->shouldReceive('getQueryParams')->andReturn([]);

        RequestContext::set($psrRequest);
        $request = new DemoRequest(Mockery::mock(ContainerInterface::class));

        $this->assertEquals(['file' => ['Invalid File.', $file]], $request->getValidationData());
    }

    public function testRewriteGetRules()
    {
        $psrRequest = Mockery::mock(ServerRequestPlusInterface::class);
        $psrRequest->shouldReceive('getQueryParams')->andReturn([]);
        $psrRequest->shouldReceive('getUploadedFiles')->andReturn([]);
        $psrRequest->shouldReceive('getParsedBody')->andReturn([
            'name' => 'xxx',
        ]);

        RequestContext::set($psrRequest);
        ResponseContext::set(new Response());
        $container = Mockery::mock(ContainerInterface::class);
        $translator = new Translator(new ArrayLoader(), 'en');
        $container->shouldReceive('get')->with(ValidatorFactoryInterface::class)->andReturn(new ValidatorFactory($translator));

        $request = new BarSceneRequest($container);
        $res = $request->scene('required')->validated();
        $this->assertSame(['name' => 'xxx'], $res);

        try {
            $request = new BarSceneRequest($container);
            $request->validateResolved();
            $this->assertTrue(false);
        } catch (Throwable $exception) {
            $this->assertInstanceOf(ValidationException::class, $exception);
            $this->assertSame('validation.integer', $exception->validator->errors()->first());
        }
    }

    public function testSceneForFormRequest()
    {
        $psrRequest = Mockery::mock(ServerRequestPlusInterface::class);
        $psrRequest->shouldReceive('getQueryParams')->andReturn([]);
        $psrRequest->shouldReceive('getUploadedFiles')->andReturn([]);
        $psrRequest->shouldReceive('getParsedBody')->andReturn([
            'mobile' => '12345',
        ]);

        RequestContext::set($psrRequest);
        ResponseContext::set(new Response());
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(Waiter::class)->andReturn(new Waiter());
        ApplicationContext::setContainer($container);
        $translator = new Translator(new ArrayLoader(), 'en');
        $container->shouldReceive('get')->with(ValidatorFactoryInterface::class)->andReturn(new ValidatorFactory($translator));

        $request = new FooSceneRequest($container);
        $res = $request->scene('info')->validated();
        $this->assertSame(['mobile' => '12345'], $res);

        wait(function () use ($request, $psrRequest) {
            Context::set(ServerRequestInterface::class, $psrRequest);
            Context::set(ResponseInterface::class, new Response());
            try {
                $request->validateResolved();
                $this->assertTrue(false);
            } catch (Throwable $exception) {
                $this->assertInstanceOf(ValidationException::class, $exception);
            }
        });

        try {
            $request = new FooSceneRequest($container);
            $request->validateResolved();
            $this->assertTrue(false);
        } catch (Throwable $exception) {
            $this->assertInstanceOf(ValidationException::class, $exception);
        }
    }
}
