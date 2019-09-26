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
use Hyperf\Guzzle\CoroutineHandler;
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
        $this->assertTrue($stack->hasHandler());

        $ref = new \ReflectionClass($stack);

        $handler = $ref->getProperty('handler');
        $handler->setAccessible(true);
        $this->assertInstanceOf(CoroutineHandler::class, $handler->getValue($stack));

        $property = $ref->getProperty('stack');
        $property->setAccessible(true);
        foreach ($property->getValue($stack) as $stack) {
            $this->assertTrue(in_array($stack[1], ['http_errors', 'allow_redirects', 'cookies', 'prepare_body', 'retry']));
        }
    }

    // public function testCreatePoolHandler()
    // {
    //     $this->getContainer();
    //
    //     $factory = new HandlerStackFactory();
    //
    //     $stack = $factory->create();
    //
    //     $this->assertInstanceOf(HandlerStack::class, $stack);
    // }
    //
    // protected function getContainer()
    // {
    //     $container = Mockery::mock(Container::class);
    // }
}
