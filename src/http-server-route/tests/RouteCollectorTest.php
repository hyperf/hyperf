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
namespace HyperfTest\HttpServerRoute;

use Hyperf\HttpServerRoute\RouteCollector;
use HyperfTest\HttpServerRoute\Stub\ContainerStub;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class RouteCollectorTest extends TestCase
{
    public function testGetPath()
    {
        $container = ContainerStub::getContainer();
        $collector = new RouteCollector($container);
        $this->assertSame('/', $collector->getPath('index'));
        $this->assertSame('/user/123', $collector->getPath('user.info', ['id' => 123]));
        $this->assertSame('/user', $collector->getPath('user.list'));
        $this->assertSame('/author/Hyperf/book/PHP', $collector->getPath('author.book', ['user' => 'Hyperf', 'name' => 'PHP']));
        $this->assertSame('/author/Hyperf', $collector->getPath('author.role', ['user' => 'Hyperf']));
        $this->assertSame('/author/Hyperf/role/master', $collector->getPath('author.role', ['user' => 'Hyperf', 'name' => 'master']));
        $this->assertSame('/book', $collector->getPath('book.author'));
    }
}
