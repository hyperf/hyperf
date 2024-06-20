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

namespace HyperfTest\Config;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function Hyperf\Config\config;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ConfigTest extends TestCase
{
    public function testConfigFunction()
    {
        ApplicationContext::setContainer($container = Mockery::mock(ContainerInterface::class));
        $container->shouldReceive('has')->with(ConfigInterface::class)->andReturnTrue();
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'id' => $id = uniqid(),
        ]));

        $this->assertSame($id, config('id'));
    }
}
