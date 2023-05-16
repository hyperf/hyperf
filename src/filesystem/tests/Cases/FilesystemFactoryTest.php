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
namespace HyperfTest\Filesystem\Cases;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use Hyperf\Filesystem\Adapter\AliyunOssAdapterFactory;
use Hyperf\Filesystem\Adapter\LocalAdapterFactory;
use Hyperf\Filesystem\Adapter\MemoryAdapterFactory;
use Hyperf\Filesystem\FilesystemFactory;
use Hyperf\Filesystem\FilesystemInvoker;
use Hyperf\Filesystem\Version;
use Hyperf\Flysystem\OSS\Adapter as OSSAdapter;
use Hyperf\Support\Reflection\ClassInvoker;
use League\Flysystem\Adapter\Ftp;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;
use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\Memory\MemoryAdapter;
use Overtrue\Flysystem\Cos\CosAdapter;
use Overtrue\Flysystem\Qiniu\QiniuAdapter;
use Xxtime\Flysystem\Aliyun\OssAdapter as XxtimeOSSAdapter;

! defined('BASE_PATH') && define('BASE_PATH', '.');

/**
 * @internal
 * @coversNothing
 */
class FilesystemFactoryTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        $container = new Container(new DefinitionSource([]));
        ApplicationContext::setContainer($container);
    }

    public function testGet()
    {
        $config = new Config([
            'file' => [
                'default' => 'local',
                'storage' => [
                    'local' => [
                        'driver' => LocalAdapterFactory::class,
                    ],
                    'test' => [
                        'driver' => MemoryAdapterFactory::class,
                    ],
                ],
            ],
        ]);
        $container = ApplicationContext::getContainer();
        $container->set(ConfigInterface::class, $config);
        $factory = new FilesystemFactory($container, $container->get(ConfigInterface::class));
        $this->assertInstanceOf(\League\Flysystem\Filesystem::class, $fileSystem = $factory->get('test'));
        if (Version::isV2()) {
            $invoker = new ClassInvoker($fileSystem);
            $this->assertInstanceOf(InMemoryFilesystemAdapter::class, $invoker->adapter);
        } else {
            $this->assertInstanceOf(MemoryAdapter::class, $fileSystem->getAdapter());
        }
    }

    public function testDefault()
    {
        $config = new Config([
            'file' => [
                'default' => 'local',
                'storage' => [
                    'local' => [
                        'driver' => LocalAdapterFactory::class,
                        'root' => '.',
                    ],
                    'test' => [
                        'driver' => MemoryAdapterFactory::class,
                    ],
                ],
            ],
        ]);
        $container = ApplicationContext::getContainer();
        $container->set(ConfigInterface::class, $config);
        $container->define(Filesystem::class, FilesystemInvoker::class);
        $fileSystem = $container->get(Filesystem::class);
        $this->assertInstanceOf(\League\Flysystem\Filesystem::class, $fileSystem);
        if (Version::isV2()) {
            $invoker = new ClassInvoker($fileSystem);
            $this->assertInstanceOf(LocalFilesystemAdapter::class, $invoker->adapter);
        } else {
            $this->assertInstanceOf(Local::class, $fileSystem->getAdapter());
        }
    }

    public function testMissingConfiguration()
    {
        $config = new Config([]);
        $container = ApplicationContext::getContainer();
        $container->set(ConfigInterface::class, $config);
        $container->define(Filesystem::class, FilesystemInvoker::class);
        $fileSystem = $container->get(Filesystem::class);
        $this->assertInstanceOf(\League\Flysystem\Filesystem::class, $fileSystem);
        if (Version::isV2()) {
            $invoker = new ClassInvoker($fileSystem);
            $this->assertInstanceOf(LocalFilesystemAdapter::class, $invoker->adapter);
        } else {
            $this->assertInstanceOf(Local::class, $fileSystem->getAdapter());
        }
    }

    public function testFtpAdapter()
    {
        if (! class_exists(FtpAdapter::class) && ! class_exists(Ftp::class)) {
            $this->markTestSkipped('Ftp Adapter does not exists.');
        }
        $config = new Config([
            'file' => [
                'default' => 'ftp',
                'storage' => [
                    'ftp' => [
                        'driver' => \Hyperf\Filesystem\Adapter\FtpAdapterFactory::class,
                        'host' => 'ftp.example.com',
                        'username' => 'username',
                        'password' => 'password',
                    ],
                ],
            ],
        ]);
        $container = ApplicationContext::getContainer();
        $container->set(ConfigInterface::class, $config);
        $container->define(Filesystem::class, FilesystemInvoker::class);
        $fileSystem = $container->get(Filesystem::class);
        $this->assertInstanceOf(\League\Flysystem\Filesystem::class, $fileSystem);
        if (Version::isV2()) {
            $invoker = new ClassInvoker($fileSystem);
            $this->assertInstanceOf(FtpAdapter::class, $invoker->adapter);
        } else {
            $this->assertInstanceOf(Ftp::class, $fileSystem->getAdapter());
        }
    }

    public function testAliyunOSSAdapter()
    {
        if (! class_exists(OSSAdapter::class) && ! class_exists(XxtimeOSSAdapter::class)) {
            $this->markTestSkipped('OSS Adapter does not exists.');
        }
        $config = new Config([
            'file' => [
                'default' => 'oss',
                'storage' => [
                    'oss' => [
                        'driver' => AliyunOssAdapterFactory::class,
                        'accessId' => 'xxx',
                        'accessSecret' => 'xxx',
                        'bucket' => 'hyperf',
                        'endpoint' => null,
                    ],
                ],
            ],
        ]);
        $container = ApplicationContext::getContainer();
        $container->set(ConfigInterface::class, $config);
        $container->define(Filesystem::class, FilesystemInvoker::class);
        $fileSystem = $container->get(Filesystem::class);
        $this->assertInstanceOf(\League\Flysystem\Filesystem::class, $fileSystem);
        if (Version::isV2()) {
            $invoker = new ClassInvoker($fileSystem);
            $this->assertInstanceOf(OSSAdapter::class, $invoker->adapter);
        } else {
            $this->assertInstanceOf(XxtimeOSSAdapter::class, $fileSystem->getAdapter());
        }
    }

    public function testS3Adapter()
    {
        if (! class_exists(AwsS3V3Adapter::class) && ! class_exists(AwsS3Adapter::class)) {
            $this->markTestSkipped('OSS Adapter does not exists.');
        }
        $config = new Config([
            'file' => [
                'default' => 's3',
                'storage' => [
                    's3' => [
                        'driver' => \Hyperf\Filesystem\Adapter\S3AdapterFactory::class,
                        'credentials' => [
                            'key' => 'xxx',
                            'secret' => 'xxx',
                        ],
                        'region' => 'shanghai',
                        'version' => 'latest',
                        'bucket_endpoint' => false,
                        'use_path_style_endpoint' => false,
                        'endpoint' => 'xxx',
                        'bucket_name' => 'xxx',
                    ],
                ],
            ],
        ]);
        $container = ApplicationContext::getContainer();
        $container->set(ConfigInterface::class, $config);
        $container->define(Filesystem::class, FilesystemInvoker::class);
        $fileSystem = $container->get(Filesystem::class);
        $this->assertInstanceOf(\League\Flysystem\Filesystem::class, $fileSystem);
        if (Version::isV2()) {
            $invoker = new ClassInvoker($fileSystem);
            $this->assertInstanceOf(AwsS3V3Adapter::class, $invoker->adapter);
        } else {
            $this->assertInstanceOf(AwsS3Adapter::class, $fileSystem->getAdapter());
        }
    }

    public function testCosAdapter()
    {
        if (! class_exists(CosAdapter::class)) {
            $this->markTestSkipped('COS Adapter does not exists.');
        }
        $config = new Config([
            'file' => [
                'default' => 'cos',
                'storage' => [
                    'cos' => [
                        'driver' => \Hyperf\Filesystem\Adapter\CosAdapterFactory::class,
                        'region' => 'xxx',
                        'app_id' => 'xxx',
                        'secret_id' => 'xxx',
                        'secret_key' => 'xxx',
                        'bucket' => 'hyperf',
                        'read_from_cdn' => false,
                    ],
                ],
            ],
        ]);
        $container = ApplicationContext::getContainer();
        $container->set(ConfigInterface::class, $config);
        $container->define(Filesystem::class, FilesystemInvoker::class);
        $fileSystem = $container->get(Filesystem::class);
        $this->assertInstanceOf(\League\Flysystem\Filesystem::class, $fileSystem);
        if (Version::isV2()) {
            $invoker = new ClassInvoker($fileSystem);
            $this->assertInstanceOf(CosAdapter::class, $invoker->adapter);
        } else {
            $this->assertInstanceOf(CosAdapter::class, $fileSystem->getAdapter());
        }
    }

    public function testQiniuAdapter()
    {
        if (! class_exists(QiniuAdapter::class)) {
            $this->markTestSkipped('Qiniu Adapter does not exists.');
        }
        $config = new Config([
            'file' => [
                'default' => 'qiniu',
                'storage' => [
                    'qiniu' => [
                        'driver' => \Hyperf\Filesystem\Adapter\QiniuAdapterFactory::class,
                        'accessKey' => 'xxx',
                        'secretKey' => 'xxx',
                        'bucket' => 'xxx',
                        'domain' => 'xxx',
                    ],
                ],
            ],
        ]);
        $container = ApplicationContext::getContainer();
        $container->set(ConfigInterface::class, $config);
        $container->define(Filesystem::class, FilesystemInvoker::class);
        $fileSystem = $container->get(Filesystem::class);
        $this->assertInstanceOf(\League\Flysystem\Filesystem::class, $fileSystem);
        if (Version::isV2()) {
            $invoker = new ClassInvoker($fileSystem);
            $this->assertInstanceOf(QiniuAdapter::class, $invoker->adapter);
        } else {
            $this->assertInstanceOf(QiniuAdapter::class, $fileSystem->getAdapter());
        }
    }
}
