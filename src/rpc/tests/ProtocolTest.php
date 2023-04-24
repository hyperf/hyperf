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
namespace HyperfTest\Rpc;

use Hyperf\Config\Config;
use Hyperf\Rpc\Protocol;
use Hyperf\Rpc\ProtocolManager;
use Hyperf\Serializer\JsonDeNormalizer;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class ProtocolTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testNormalizer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->withAnyArgs()->andReturn(true);
        $container->shouldReceive('get')->with(JsonDeNormalizer::class)->andReturn($assert = new JsonDeNormalizer());
        $manager = new ProtocolManager(new Config([]));
        $manager->register('test', [
            'normalizer' => JsonDeNormalizer::class,
        ]);

        $protocol = new Protocol($container, $manager, 'test');
        $normalizer = $protocol->getNormalizer();
        $this->assertSame($assert, $normalizer);
    }
}
