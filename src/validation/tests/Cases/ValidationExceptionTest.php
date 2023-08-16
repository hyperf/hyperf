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
use Hyperf\Contract\TranslatorInterface;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\HttpMessage\Base\Response;
use Hyperf\Support\MessageBag;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException;
use Hyperf\Validation\ValidationExceptionHandler;
use Hyperf\Validation\ValidatorFactory;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class ValidationExceptionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testWithMessages()
    {
        $this->getContainer();
        $exception = ValidationException::withMessages([
            'id' => 'id is required.',
            'name' => ['name is required.'],
        ]);

        $this->assertInstanceOf(ValidationException::class, $exception);
        $errors = $exception->validator->errors();
        $this->assertInstanceOf(MessageBag::class, $errors);
        $this->assertEquals([
            'id' => ['id is required.'],
            'name' => ['name is required.'],
        ], $errors->getMessages());
    }

    public function testValidationExceptionHandler()
    {
        $handler = new ValidationExceptionHandler();
        $validator = Mockery::mock(ValidatorInterface::class);
        $validator->shouldReceive('errors')->andReturnUsing(function () {
            $message = Mockery::mock(MessageBag::class);
            $message->shouldReceive('first')->andReturn('id is required');
            return $message;
        });
        $exception = new ValidationException($validator);
        $response = new Response();
        $response = $handler->handle($exception, $response);
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('id is required', $response->getBody()->getContents());
        $this->assertSame('text/plain; charset=utf-8', $response->getHeaderLine('content-type'));

        $response = (new Response())->withAddedHeader('Content-Type', 'application/json; charset=utf-8');
        $exception = new ValidationException($validator);
        $response = $handler->handle($exception, $response);
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('id is required', $response->getBody()->getContents());
        $this->assertSame('application/json; charset=utf-8', $response->getHeaderLine('content-type'));
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('get')->with(ValidatorFactoryInterface::class)->andReturnUsing(function () use ($container) {
            $translator = Mockery::mock(TranslatorInterface::class);
            return new ValidatorFactory($translator, $container);
        });

        return $container;
    }
}
