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

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\RateLimit\Aspect\RateLimitAnnotationAspect;
use Hyperf\RateLimit\Handler\RateLimitHandler;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
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

    /**
     * @deprecated
     */
    public function testAspectConfigDeprecated()
    {
        $container = $this->getContainer();
        $container->shouldReceive('has')->andReturn(true);
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturnUsing(function () {
            $logger = Mockery::mock(StdoutLoggerInterface::class);
            $logger->shouldReceive('warning')->andReturnUsing(function ($message) {
                $this->assertSame('Config rate-limit.php will be removed in v1.2, please use rate_limit.php instead.', $message);
            });

            return $logger;
        });

        $request = Mockery::mock(RequestInterface::class);
        $handler = Mockery::mock(RateLimitHandler::class);
        $aspect = new RateLimitAnnotationAspect(new Config($config = [
            'rate-limit' => [
                'create' => 1,
                'consume' => 1,
                'capacity' => 2,
                'limitCallback' => [],
                'waitTimeout' => 1,
            ],
        ]), $request, $handler);

        $this->assertSame($config['rate-limit'], $aspect->getConfig());
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        return $container;
    }
}
