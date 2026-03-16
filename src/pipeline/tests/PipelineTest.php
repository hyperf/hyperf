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

namespace HyperfTest\Pipeline;

use Hyperf\Context\ApplicationContext;
use Hyperf\Pipeline\Pipeline;
use HyperfTest\Pipeline\Stub\FooPipeline;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class PipelineTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testPipelineBasicUsage()
    {
        $pipeTwo = function ($piped, $next) {
            $_SERVER['__test.pipe.two'] = $piped;

            return $next($piped);
        };

        $result = (new Pipeline($this->getContainer()))
            ->send('foo')
            ->through([PipelineTestPipeOne::class, $pipeTwo])
            ->then(function ($piped) {
                return $piped;
            });

        $this->assertSame('foo', $result);
        $this->assertSame('foo', $_SERVER['__test.pipe.one']);
        $this->assertSame('foo', $_SERVER['__test.pipe.two']);

        unset($_SERVER['__test.pipe.one'], $_SERVER['__test.pipe.two']);
    }

    public function testPipelineUsageWithObjects()
    {
        $result = (new Pipeline($this->getContainer()))
            ->send('foo')
            ->through([new PipelineTestPipeOne()])
            ->then(function ($piped) {
                return $piped;
            });

        $this->assertSame('foo', $result);
        $this->assertSame('foo', $_SERVER['__test.pipe.one']);

        unset($_SERVER['__test.pipe.one']);
    }

    public function testPipelineUsageWithInvokableObjects()
    {
        $result = (new Pipeline($this->getContainer()))
            ->send('foo')
            ->through([new PipelineTestPipeTwo()])
            ->then(
                function ($piped) {
                    return $piped;
                }
            );

        $this->assertSame('foo', $result);
        $this->assertSame('foo', $_SERVER['__test.pipe.one']);

        unset($_SERVER['__test.pipe.one']);
    }

    public function testPipelineUsageWithCallable()
    {
        $function = function ($piped, $next) {
            $_SERVER['__test.pipe.one'] = 'foo';

            return $next($piped);
        };

        $result = (new Pipeline($this->getContainer()))
            ->send('foo')
            ->through([$function])
            ->then(
                function ($piped) {
                    return $piped;
                }
            );

        $this->assertSame('foo', $result);
        $this->assertSame('foo', $_SERVER['__test.pipe.one']);

        unset($_SERVER['__test.pipe.one']);

        $result = (new Pipeline($this->getContainer()))
            ->send('bar')
            ->through($function)
            ->then(static function ($passable) {
                return $passable;
            });

        $this->assertSame('bar', $result);
        $this->assertSame('foo', $_SERVER['__test.pipe.one']);

        unset($_SERVER['__test.pipe.one']);
    }

    public function testPipelineUsageWithInvokableClass()
    {
        $result = (new Pipeline($this->getContainer()))
            ->send('foo')
            ->through([PipelineTestPipeTwo::class])
            ->then(
                function ($piped) {
                    return $piped;
                }
            );

        $this->assertSame('foo', $result);
        $this->assertSame('foo', $_SERVER['__test.pipe.one']);

        unset($_SERVER['__test.pipe.one']);
    }

    public function testPipelineUsageWithParameters()
    {
        $parameters = ['one', 'two'];

        $result = (new Pipeline($this->getContainer()))
            ->send('foo')
            ->through(PipelineTestParameterPipe::class . ':' . implode(',', $parameters))
            ->then(function ($piped) {
                return $piped;
            });

        $this->assertSame('foo', $result);
        $this->assertEquals($parameters, $_SERVER['__test.pipe.parameters']);

        unset($_SERVER['__test.pipe.parameters']);
    }

    public function testPipelineViaChangesTheMethodBeingCalledOnThePipes()
    {
        $pipelineInstance = new Pipeline($this->getContainer());
        $result = $pipelineInstance->send('data')
            ->through(PipelineTestPipeOne::class)
            ->via('differentMethod')
            ->then(function ($piped) {
                return $piped;
            });
        $this->assertSame('data', $result);
    }

    public function testPipelineThenMethodRunsPipelineThenReturnsPassable()
    {
        $result = (new Pipeline($this->getContainer()))
            ->send('foo')
            ->through([PipelineTestPipeOne::class])
            ->then(static function ($passable) {
                return $passable;
            });

        $this->assertSame('foo', $result);
        $this->assertSame('foo', $_SERVER['__test.pipe.one']);

        unset($_SERVER['__test.pipe.one']);
    }

    public function testPipelineThenReturnMethodRunsPipelineThenReturnsTheResult()
    {
        $result = (new Pipeline($this->getContainer()))
            ->send('foo')
            ->through([PipelineTestPipeOne::class])
            ->thenReturn();

        $this->assertSame('foo', $result);
        $this->assertSame('foo', $_SERVER['__test.pipe.one']);

        unset($_SERVER['__test.pipe.one']);
    }

    public function testHandleCarry()
    {
        $result = (new FooPipeline($this->getContainer()))
            ->send($id = rand(0, 99))
            ->through([PipelineTestPipeOne::class])
            ->via('incr')
            ->then(static function ($passable) {
                if (is_int($passable)) {
                    $passable += 3;
                }
                return $passable;
            });

        $this->assertSame($id + 6, $result);
    }

    public function testPipelineMacro()
    {
        Pipeline::macro('customMethod', function ($value) {
            return 'custom_' . $value;
        });

        $pipeline = new Pipeline($this->getContainer());
        $this->assertTrue($pipeline->hasMacro('customMethod'));
        $this->assertSame('custom_test', $pipeline->customMethod('test'));
    }

    public function testPipelineMacroWithThis()
    {
        Pipeline::macro('getPipes', function () {
            return $this->pipes;
        });

        $pipeline = new Pipeline($this->getContainer());
        $pipeline->through(['pipe1', 'pipe2']);

        $this->assertEquals(['pipe1', 'pipe2'], $pipeline->getPipes());
    }

    public function testPipelineHasMacro()
    {
        Pipeline::macro('existingMacro', function () {
            return 'exists';
        });

        $pipeline = new Pipeline($this->getContainer());

        $this->assertTrue($pipeline->hasMacro('existingMacro'));
        $this->assertFalse($pipeline->hasMacro('nonExistingMacro'));
    }

    public function testPipelineMacroOverwrite()
    {
        Pipeline::macro('testMacro', function () {
            return 'first';
        });

        $pipeline = new Pipeline($this->getContainer());
        $this->assertSame('first', $pipeline->testMacro());

        Pipeline::macro('testMacro', function () {
            return 'second';
        });

        $pipeline2 = new Pipeline($this->getContainer());
        $this->assertSame('second', $pipeline2->testMacro());
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);
        $container->shouldReceive('get')->with(PipelineTestPipeOne::class)->andReturn(new PipelineTestPipeOne());
        $container->shouldReceive('get')->with(PipelineTestPipeTwo::class)->andReturn(new PipelineTestPipeTwo());
        $container->shouldReceive('get')->with(PipelineTestParameterPipe::class)->andReturn(new PipelineTestParameterPipe());

        return $container;
    }
}

class PipelineTestPipeOne
{
    public function handle($piped, $next)
    {
        $_SERVER['__test.pipe.one'] = $piped;

        return $next($piped);
    }

    public function differentMethod($piped, $next)
    {
        return $next($piped);
    }

    public function incr($piped, $next)
    {
        return $next(++$piped);
    }
}

class PipelineTestPipeTwo
{
    public function __invoke($piped, $next)
    {
        $_SERVER['__test.pipe.one'] = $piped;

        return $next($piped);
    }
}

class PipelineTestParameterPipe
{
    public function handle($piped, $next, $parameter1 = null, $parameter2 = null)
    {
        $_SERVER['__test.pipe.parameters'] = [$parameter1, $parameter2];

        return $next($piped);
    }
}
