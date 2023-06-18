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
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\RateLimit\Aspect\RateLimitAnnotationAspect;
use Hyperf\RateLimit\Handler\RateLimitHandler;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

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

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        return $container;
    }
}
