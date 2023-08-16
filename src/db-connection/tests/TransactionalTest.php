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
namespace HyperfTest\DbConnection;

use Hyperf\Context\ApplicationContext;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\DbConnection\Annotation\Transactional;
use Hyperf\DbConnection\Aspect\TransactionAspect;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class TransactionalTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        AnnotationCollector::clear();
    }

    public function testTransactional()
    {
        $container = $this->getContainer();
        $resolver = $container->get(ConnectionResolverInterface::class);
        $resolver->shouldReceive('connection')->with('default')->once()->andReturn($conn = Mockery::mock(ConnectionInterface::class));
        $conn->shouldReceive('transaction')->with(Mockery::any(), 1)->once();

        $transactional = new Transactional();
        $aspect = new TransactionAspect();
        $point = new ProceedingJoinPoint(static function () {
        }, 'Foo', 'bar', []);

        AnnotationCollector::set('Foo._m.bar.' . Transactional::class, $transactional);

        $aspect->process($point);

        $this->assertTrue(true);
    }

    public function testTransactionalWithArguments()
    {
        $attempts = rand(1, 5);
        $pool = uniqid();

        $container = $this->getContainer();
        $resolver = $container->get(ConnectionResolverInterface::class);
        $resolver->shouldReceive('connection')->with($pool)->once()->andReturn($conn = Mockery::mock(ConnectionInterface::class));
        $conn->shouldReceive('transaction')->with(Mockery::any(), $attempts)->once();

        $transactional = new Transactional($pool, $attempts);
        $aspect = new TransactionAspect();
        $point = new ProceedingJoinPoint(static function () {
        }, 'Foo', 'bar', []);

        AnnotationCollector::set('Foo._m.bar.' . Transactional::class, $transactional);

        $aspect->process($point);

        $this->assertTrue(true);
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('get')->with(Db::class)->andReturn(new Db($container));
        $container->shouldReceive('get')->with(ConnectionResolverInterface::class)->andReturn(Mockery::mock(ConnectionResolverInterface::class));

        return $container;
    }
}
