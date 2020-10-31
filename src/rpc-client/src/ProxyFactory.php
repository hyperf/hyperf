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

use Hyperf\RpcClient\Proxy\Ast;
use Hyperf\RpcClient\Proxy\CodeLoader;
use Hyperf\Utils\Coroutine\Locker;
use Hyperf\Utils\Filesystem\Filesystem;
use Hyperf\Utils\Traits\Container;

class ProxyFactory
{
    use Container;

    /**
     * @var Ast
     */
    protected $ast;

    /**
     * @var \Hyperf\RpcClient\Proxy\CodeLoader
     */
    protected $codeLoader;

    /**
     * @var Filesystem
     */
    protected $filesystem;

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
        $proxyClassName = $serviceClass . '_' . md5($this->codeLoader->getCodeByClassName($serviceClass));
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

        $time = $this->filesystem->lastModified(
            $this->codeLoader->getPathByClassName($interface)
        );

        return $time >= $this->filesystem->lastModified($path);
    }
}
