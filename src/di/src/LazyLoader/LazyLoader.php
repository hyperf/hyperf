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

namespace Hyperf\Di\LazyLoader;

use Hyperf\CodeParser\PhpParser;
use Hyperf\Coroutine\Locker as CoLocker;
use Hyperf\Stringable\Str;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\PrettyPrinter\Standard;
use ReflectionClass;

class LazyLoader
{
    public const CONFIG_FILE_NAME = 'lazy_loader.php';

    /**
     * Indicates if a loader has been registered.
     */
    protected bool $registered = false;

    /**
     * The singleton instance of the loader.
     */
    protected static ?LazyLoader $instance = null;

    /**
     * @param array $config the Configuration object
     */
    private function __construct(protected array $config)
    {
        $this->register();
    }

    /**
     * Get or create the singleton lazy loader instance.
     */
    public static function bootstrap(string $configDir): LazyLoader
    {
        if (static::$instance) {
            return static::$instance;
        }
        $path = $configDir . self::CONFIG_FILE_NAME;

        $config = [];
        if (file_exists($path)) {
            $config = include $path;
        }
        return static::$instance = new static($config);
    }

    /**
     * Load a class proxy if it is registered.
     *
     * @return null|bool
     */
    public function load(string $proxy)
    {
        if (array_key_exists($proxy, $this->config) || str_starts_with($proxy, 'HyperfLazy\\')) {
            $this->loadProxy($proxy);
            return true;
        }
        return null;
    }

    /**
     * Register the loader on the auto-loader stack.
     */
    protected function register(): void
    {
        if (! $this->registered) {
            $this->prependToLoaderStack();
            $this->registered = true;
        }
    }

    /**
     * Load a real-time facade for the given proxy.
     */
    protected function loadProxy(string $proxy)
    {
        require_once $this->ensureProxyExists($proxy);
    }

    /**
     * Ensure that the given proxy has an existing real-time facade class.
     */
    protected function ensureProxyExists(string $proxy): string
    {
        $dir = BASE_PATH . '/runtime/container/proxy/';
        if (! file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $code = $this->generatorLazyProxy(
            $proxy,
            $this->config[$proxy] ?? Str::after($proxy, 'HyperfLazy\\')
        );

        $path = str_replace('\\', '_', $dir . $proxy . '_' . crc32($code) . '.php');
        $key = md5($path);
        // If the proxy file does not exist, then try to acquire the coroutine lock.
        if (! file_exists($path) && CoLocker::lock($key)) {
            $targetPath = $path . '.' . uniqid();
            file_put_contents($targetPath, $code);
            rename($targetPath, $path);
            CoLocker::unlock($key);
        }
        return $path;
    }

    /**
     * Format the lazy proxy with the proper namespace and class.
     */
    protected function generatorLazyProxy(string $proxy, string $target): string
    {
        $targetReflection = new ReflectionClass($target);
        if ($this->isUnsupportedReflectionType($targetReflection)) {
            $builder = new FallbackLazyProxyBuilder();
            return $this->buildNewCode($builder, $proxy, $targetReflection);
        }
        if ($targetReflection->isInterface()) {
            $builder = new InterfaceLazyProxyBuilder();
            return $this->buildNewCode($builder, $proxy, $targetReflection);
        }
        $builder = new ClassLazyProxyBuilder();
        return $this->buildNewCode($builder, $proxy, $targetReflection);
    }

    /**
     * Prepend the load method to the auto-loader stack.
     */
    protected function prependToLoaderStack(): void
    {
        /** @var callable(string): void */
        $load = [$this, 'load'];
        spl_autoload_register($load, true, true);
    }

    /**
     * These conditions are really hard to proxy via inheritance.
     * Luckily these conditions are very rarely met.
     *
     * TODO: implement some of them.
     *
     * @param ReflectionClass $targetReflection [description]
     * @return bool [description]
     */
    private function isUnsupportedReflectionType(ReflectionClass $targetReflection): bool
    {
        return $targetReflection->isFinal();
    }

    private function buildNewCode(AbstractLazyProxyBuilder $builder, string $proxy, ReflectionClass $reflectionClass): string
    {
        $target = $reflectionClass->getName();
        $nodes = PhpParser::getInstance()->getNodesFromReflectionClass($reflectionClass);
        $builder->addClassBoilerplate($proxy, $target);
        $builder->addClassRelationship();
        $traverser = new NodeTraverser();
        $methods = PhpParser::getInstance()->getAllMethodsFromStmts($nodes);
        $visitor = new PublicMethodVisitor($methods, $builder->getOriginalClassName());
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor($visitor);
        $traverser->traverse($nodes);
        $builder->addNodes($visitor->nodes);
        $prettyPrinter = new Standard();
        return $prettyPrinter->prettyPrintFile([$builder->getNode()]);
    }
}
