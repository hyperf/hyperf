<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Encrypt;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Container;
use Hyperf\Encrypt\Handler\EncryptHandler;
use Hyperf\Encrypt\Handler\EncryptHandlerInterface;
use Hyperf\Encrypt\SecretKey;
use Hyperf\Encrypt\SecretKeyInterface;
use Hyperf\Utils\ApplicationContext;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class EncryptTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }

    public function testSignAndVerify()
    {
        $this->getContainer();
        $data = ['message' => 'Hello Hyperf.'];
        $encrypted = sign($data);
        $this->assertArrayHasKey('sign', $encrypted);
        $this->assertEquals($data, verify(http_build_query($encrypted)));
    }

    public function testEncryptAndDecrypt()
    {
        $this->getContainer();
        $data = ['message' => 'Hello Hyperf.'];
        $encrypted = encrypt($data);
        $this->assertEquals($data, decrypt($encrypted));
    }

    protected function getContainer()
    {
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('has')->andReturn(true);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
        ]));
        $secret = new SecretKey(OPENSSL_PKCS1_PADDING, __DIR__ . '/encrypt/public_key.pem', __DIR__ . '/encrypt/private_key.pem');
        $encrypt = new EncryptHandler($secret);
        $container->shouldReceive('make')->with(SecretKeyInterface::class, Mockery::any())->andReturn($secret);
        $container->shouldReceive('make')->with(EncryptHandlerInterface::class, Mockery::any())->andReturn($encrypt);

        ApplicationContext::setContainer($container);

        return $container;
    }
}
