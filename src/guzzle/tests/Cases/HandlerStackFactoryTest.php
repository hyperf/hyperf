<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Guzzle\Cases;

use GuzzleHttp\HandlerStack;
use Hyperf\Di\Container;
use Hyperf\Guzzle\HandlerStackFactory;
use Hyperf\Utils\ApplicationContext;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class HandlerStackFactoryTest extends TestCase
{
    public function testCreateCoroutineHandler()
    {
        $container = Mockery::mock(ContainerInterface::class);

        ApplicationContext::setContainer($container);

        $factory = new HandlerStackFactory();

        $stack = $factory->create();

        $this->assertInstanceOf(HandlerStack::class, $stack);
    }

    protected function getContainer()
    {
        $container = Mockery::mock(Container::class);
    }
}
