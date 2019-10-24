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

namespace Hyperf\Di;

use Hyperf\Di\Aop\Ast;
use Hyperf\Di\Definition\ObjectDefinition;
use Hyperf\Utils\Coroutine\Locker as CoLocker;

class ProxyFactory
{
    /**
     * @var array
     */
    private static $map = [];

    /**
     * @var Ast
     */
    private $ast;

    public function __construct()
    {
        $this->ast = new Ast();
    }

    public function createProxyDefinition(ObjectDefinition $definition): ObjectDefinition
    {
        $identifier = $definition->getName();
        if (isset(static::$map[$identifier])) {
            return static::$map[$identifier];
        }

        $proxyIdentifier = $definition->getClassName() . '_' . md5($definition->getClassName());
        $definition->setProxyClassName($proxyIdentifier);
        $this->loadProxy($definition->getClassName(), $definition->getProxyClassName());

        static::$map[$identifier] = $definition;
        return static::$map[$identifier];
    }

    private function loadProxy(string $className, string $proxyClassName): void
    {
        $dir = BASE_PATH . '/runtime/container/proxy/';
        if (! file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        $proxyFileName = str_replace('\\', '_', $className);
        $path = $dir . $proxyFileName . '.proxy.php';

        $key = md5($path);
        // If the proxy file does not exist, then try to acquire the coroutine lock.
        if (! file_exists($path) && CoLocker::lock($key)) {
            $targetPath = $path . '.' . uniqid();
            $code = $this->ast->proxy($className, $proxyClassName);
            file_put_contents($targetPath, $code);
            rename($targetPath, $path);
            CoLocker::unlock($key);
        }
        include_once $path;
    }
}
