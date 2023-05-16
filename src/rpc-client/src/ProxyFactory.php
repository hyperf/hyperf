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
namespace Hyperf\RpcClient;

use Hyperf\Coroutine\Locker;
use Hyperf\Coroutine\Traits\Container;
use Hyperf\Di\Annotation\ScanConfig;
use Hyperf\RpcClient\Proxy\Ast;
use Hyperf\RpcClient\Proxy\CodeLoader;
use Hyperf\Support\Filesystem\Filesystem;

class ProxyFactory
{
    use Container;

    protected Ast $ast;

    protected CodeLoader $codeLoader;

    protected Filesystem $filesystem;

    public function __construct()
    {
        $this->ast = new Ast();
        $this->codeLoader = new CodeLoader();
        $this->filesystem = new Filesystem();
    }

    public function createProxy($serviceClass): string
    {
        if (self::has($serviceClass)) {
            return (string) self::get($serviceClass);
        }
        $dir = BASE_PATH . '/runtime/container/proxy/';
        if (! file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $proxyFileName = str_replace('\\', '_', $serviceClass);
        $proxyClassName = $serviceClass . '_' . $this->codeLoader->getMd5ByClassName($serviceClass);
        $path = $dir . $proxyFileName . '.rpc-client.proxy.php';

        $key = md5($path);
        // If the proxy file does not exist, then try to acquire the coroutine lock.
        if ($this->isModified($serviceClass, $path) && Locker::lock($key)) {
            $targetPath = $path . '.' . uniqid();
            $code = $this->ast->proxy($serviceClass, $proxyClassName);
            file_put_contents($targetPath, $code);
            rename($targetPath, $path);
            Locker::unlock($key);
        }
        include_once $path;
        self::set($serviceClass, $proxyClassName);
        return $proxyClassName;
    }

    protected function isModified(string $interface, string $path): bool
    {
        if (! $this->filesystem->exists($path)) {
            return true;
        }

        if (ScanConfig::instance('')->isCacheable()) {
            return false;
        }

        $time = $this->filesystem->lastModified(
            $this->codeLoader->getPathByClassName($interface)
        );

        return $time >= $this->filesystem->lastModified($path);
    }
}
