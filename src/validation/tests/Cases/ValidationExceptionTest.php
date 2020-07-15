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

use Hyperf\Contract\TranslatorInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\MessageBag;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException;
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
    protected function tearDown()
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
