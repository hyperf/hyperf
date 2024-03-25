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

namespace HyperfTest\RateLimit;

use bandwidthThrottle\tokenBucket\Rate;
use bandwidthThrottle\tokenBucket\TokenBucket;
use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\RateLimit\Aspect\RateLimitAnnotationAspect;
use Hyperf\RateLimit\Handler\RateLimitHandler;
use Hyperf\RateLimit\Storage\StorageInterface;
use HyperfTest\RateLimit\Stub\Storage\EmptyStorage;
use HyperfTest\RateLimit\Stub\Storage\InvalidStorage;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class RateLimitTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testAspectConfig()
    {
        $request = Mockery::mock(RequestInterface::class);
        $handler = Mockery::mock(RateLimitHandler::class);
        $aspect = new RateLimitAnnotationAspect(new Config($config = [
            'rate_limit' => [
                'create' => 1,
                'consume' => 1,
                'capacity' => 2,
                'limitCallback' => [],
                'waitTimeout' => 1,
            ],
        ]), $request, $handler);

        $this->assertSame($config['rate_limit'], $aspect->getConfig());
    }

    public function testValidStorage()
    {
        $container = $this->getContainer();
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'rate_limit' => [
                'storage' => [
                    'class' => EmptyStorage::class,
                ],
            ],
        ]));
        $container->shouldReceive('make')->with(EmptyStorage::class, Mockery::any())->andReturn(new EmptyStorage(
            $container,
            'empty storage',
            1,
            []
        ));
        $container->shouldReceive('make')->with(Rate::class, Mockery::any())->andReturn(new Rate(1, Rate::SECOND));
        $container->shouldReceive('make')->with(TokenBucket::class, Mockery::any())
            ->andReturnUsing(function ($class, $args) {
                return new TokenBucket(...$args);
            });

        $rateLimitHandler = new RateLimitHandler($container);
        $rateLimitHandler->build('test', 1, 1, 1);

        // 断言正常结束
        $this->assertTrue(true);
    }

    public function testInvalidStorage()
    {
        $container = $this->getContainer();
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'rate_limit' => [
                'storage' => [
                    'class' => 'InvalidStorage',
                ],
            ],
        ]));
        $container->shouldReceive('make')->with('InvalidStorage', Mockery::any())->andReturn(new InvalidStorage());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The storage of rate limit must be an instance of ' . StorageInterface::class);

        $rateLimitHandler = new RateLimitHandler($container);
        $rateLimitHandler->build('test', 1, 1, 1);
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        return $container;
    }
}
