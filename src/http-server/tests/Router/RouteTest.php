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
namespace HyperfTest\HttpServer\Router;

use FastRoute\Dispatcher;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\HttpServer\Router\Handler;
use Hyperf\HttpServer\Router\Route;
use Hyperf\Utils\Context;
use HyperfTest\HttpServer\Stub\ContainerStub;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal
 * @coversNothing
 */
class RouteTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
        Context::set(ServerRequestInterface::class, null);
    }

    public function testGetPath()
    {
        $container = ContainerStub::getContainer();
        $collector = new Route($container);
        $this->assertSame('/', $collector->getPath('index'));
        $this->assertSame('/user/123', $collector->getPath('user.info', ['id' => 123]));
        $this->assertSame('/user', $collector->getPath('user.list'));
        $this->assertSame('/author/Hyperf/book/PHP', $collector->getPath('author.book', ['user' => 'Hyperf', 'name' => 'PHP']));
        $this->assertSame('/author/Hyperf', $collector->getPath('author.role', ['user' => 'Hyperf']));
        $this->assertSame('/author/Hyperf/role/master', $collector->getPath('author.role', ['user' => 'Hyperf', 'name' => 'master']));
        $this->assertSame('/book', $collector->getPath('book.author'));
    }

    public function testGetName()
    {
        $container = ContainerStub::getContainer();
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getAttribute')->with(Dispatched::class)->andReturnUsing(function () {
            return new Dispatched([
                Dispatcher::FOUND,
                new Handler([], '/', ['name' => 'index']),
                [
                    'id' => uniqid(),
                ],
            ]);
        });
        Context::set(ServerRequestInterface::class, $request);
        $context = new Route($container);
        $this->assertSame('index', $context->getName());
    }
}
