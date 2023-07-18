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
namespace HyperfTest\Encryption\Cases;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Container;
use Hyperf\Encryption\Encrypter;
use Hyperf\Encryption\EncrypterFactory;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class EncrypterTest extends TestCase
{
    public function testEncryptAndDecrypt()
    {
        $container = $this->getContainer();
        $encrypter = $container->get(Encrypter::class);

        $data = [
            'name' => 'hyperf',
            'sex' => 1,
        ];

        $encryptData = $encrypter->encrypt($data);
        $decryptData = $encrypter->decrypt($encryptData);

        $this->assertSame($data, $decryptData);
    }

    public function testEncryptStringAndDecryptString()
    {
        $container = $this->getContainer();
        $encrypter = $container->get(Encrypter::class);

        $data = 'hyperf';

        $encryptData = $encrypter->encryptString($data);
        $decryptData = $encrypter->decryptString($encryptData);

        $this->assertSame($data, $decryptData);
    }

    protected function getContainer()
    {
        $container = Mockery::mock(Container::class);
        $config = new Config([
            'encryption' => [
                'key' => 'base64:aQNf3TQ5r8Troe01OvQnGxP3E82ugPBCOgXm0dMfgrU=',
                'cipher' => 'AES-256-CBC',
            ],
        ]);

        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);
        $container->shouldReceive('get')->with(Encrypter::class)->andReturnUsing(function () use ($container) {
            return (new EncrypterFactory())($container);
        });

        ApplicationContext::setContainer($container);

        return $container;
    }
}
