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
namespace HyperfTest\Encryption;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Encryption\Contract\EncrypterInterface;
use Hyperf\Encryption\EncrypterFactory;
use Hyperf\Encryption\EncrypterInvoker;
use Hyperf\Utils\ApplicationContext;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class EncrypterTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function mockContainer(): ContainerInterface
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);
        $config = new Config([
            'encryption' => [
                'default' => [
                    'key' => '123456',
                    'cipher' => 'AES-256-CBC',
                ],
            ],
        ]);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);
        $container->shouldReceive('get')->with(EncrypterFactory::class)->andReturnUsing(function () use ($container) {
            return new EncrypterFactory($container);
        });
        $container->shouldReceive('get')->with(EncrypterInterface::class)->andReturnUsing(function () use ($container) {
            return (new EncrypterInvoker())($container);
        });
        return $container;
    }

    public function testEncodeString()
    {
        $input = 'hello word';
        $container = $this->mockContainer();
        $encrypter = $container->get(EncrypterInterface::class);
        $encrypt = $encrypter->encryptString($input);
        $this->assertSame($input, $encrypter->decryptString($encrypt));
    }

    public function testEncodeObject()
    {
        $input = range(1, 3);
        $container = $this->mockContainer();
        $encrypter = $container->get(EncrypterInterface::class);
        $encrypt = $encrypter->encrypt($input);
        $this->assertSame($input, $encrypter->decrypt($encrypt));
    }
}
