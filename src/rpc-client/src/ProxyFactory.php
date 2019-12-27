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

namespace Hyperf\RpcClient;

use Hyperf\RpcClient\Proxy\Ast;
use Hyperf\Utils\Coroutine\Locker;
use Hyperf\Utils\Traits\Container;

class ProxyFactory
{
    use Container;

    /**
     * @var Ast
     */
    private $ast;

    public function __construct()
    {
        $this->ast = new Ast();
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
        $proxyClassName = $serviceClass . '_' . md5($this->ast->getCodeByClassName($serviceClass));
        $path = $dir . $proxyFileName . '.proxy.php';

        $key = md5($path);
        // If the proxy file does not exist, then try to acquire the coroutine lock.
        if (! file_exists($path) && Locker::lock($key)) {
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
}
