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

namespace HyperfTest\View;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\HttpMessage\Server\Response;
use Hyperf\Task\Task;
use Hyperf\Task\TaskExecutor;
use Hyperf\View\Engine\SmartyEngine;
use Hyperf\View\Exception\RenderException;
use Hyperf\View\Mode;
use Hyperf\View\Render;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class RenderTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        Context::set(ResponseInterface::class, null);
    }

    public function testRenderException()
    {
        $container = $this->getContainer();
        Context::set(ResponseInterface::class, new Response());
        $render = new Render($container, new Config([
            'view' => [
                'engine' => SmartyEngine::class,
                'mode' => Mode::SYNC,
                'config' => [
                    'view_path' => __DIR__ . '/tpl',
                    'cache_path' => __DIR__ . '/runtime',
                ],
            ],
        ]));

        try {
            $render->getContents('index2.tpl', ['title' => 'Hyperf']);
            $this->assertTrue(false);
        } catch (Throwable $throwable) {
            $this->assertInstanceOf(RenderException::class, $throwable);
            $this->assertSame("Unable to load template 'file:index2.tpl'", $throwable->getMessage());
            $this->assertNotNull($throwable->getPrevious());
        }
    }

    public function testRender()
    {
        $container = $this->getContainer();

        foreach ([Mode::TASK, Mode::SYNC] as $mode) {
            Context::set(ResponseInterface::class, new Response());
            $render = new Render($container, new Config([
                'view' => [
                    'engine' => SmartyEngine::class,
                    'mode' => $mode,
                    'config' => [
                        'view_path' => __DIR__ . '/tpl',
                        'cache_path' => __DIR__ . '/runtime',
                    ],
                ],
            ]));

            $response = $render->render('index.tpl', ['name' => 'Hyperf']);

            $this->assertInstanceOf(ResponseInterface::class, $response);
            $this->assertSame('text/html', $response->getHeaderLine('content-type'));
            $this->assertEquals('<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hyperf</title>
</head>
<body>
Hello, Hyperf. You are using smarty template now.
</body>
</html>', (string) $response->getBody());
        }
    }

    public function testGetContents()
    {
        $container = $this->getContainer();

        foreach ([Mode::TASK, Mode::SYNC] as $mode) {
            $render = new Render($container, new Config([
                'view' => [
                    'engine' => SmartyEngine::class,
                    'mode' => $mode,
                    'config' => [
                        'view_path' => __DIR__ . '/tpl',
                        'cache_path' => __DIR__ . '/runtime',
                    ],
                ],
            ]));

            $data = $render->getContents('index.tpl', ['name' => 'Hyperf']);

            $this->assertEquals('<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hyperf</title>
</head>
<body>
Hello, Hyperf. You are using smarty template now.
</body>
</html>', $data);
        }
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('has')->andReturn(true);
        $container->shouldReceive('get')->with(SmartyEngine::class)->andReturn(new SmartyEngine());
        $container->shouldReceive('get')->with(TaskExecutor::class)->andReturnUsing(function ($_) use ($container) {
            $executor = Mockery::mock(TaskExecutor::class);
            $executor->shouldReceive('execute')->andReturnUsing(function (Task $task) use ($container) {
                [$engine, $method] = $task->callback;
                return $container->get($engine)->{$method}(...$task->arguments);
            });
            return $executor;
        });
        return $container;
    }
}
