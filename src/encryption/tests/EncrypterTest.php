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
namespace HyperfTest;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Encryption\Encrypter;
use PHPStan\Testing\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class EncrypterTest extends TestCase
{
    /**
     * @return ContainerInterface|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    public static function mockContainer()
    {
        $container = \Mockery::mock(ContainerInterface::class);
        $config = new Config([
            'encrypter' => [
                'key' => '123456',
                'cipher' => 'AES-256-CBC',
            ],
        ]);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);
        return $container;
    }

    public function testEncodeString()
    {
        $input = 'hello word';
        $container = self::mockContainer();
        $model = new Encrypter($container);
        $encrypt = $model->encryptString($input);
        $this->assertSame($input, $model->decryptString($encrypt));
    }

    public function testEncodeObject()
    {
        $input = range(1, 3);
        $container = self::mockContainer();
        $model = new Encrypter($container);
        $encrypt = $model->encrypt($input);
        $this->assertSame($input, $model->decrypt($encrypt));
    }
}
