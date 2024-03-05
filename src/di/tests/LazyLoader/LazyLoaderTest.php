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
namespace HyperfTest\Di\LazyLoader;

use Hyperf\Di\LazyLoader\LazyLoader;
use HyperfTest\Di\Stub\LazyLoad\FooImplLazyLoad;
use HyperfTest\Di\Stub\LazyLoad\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class LazyLoaderTest extends TestCase
{
    public function testGeneratorLazyProxyInterface()
    {
        $lazyLoader = LazyLoader::bootstrap(BASE_PATH);
        $proxyCode = file_get_contents(__DIR__ . '/FooProxy.txt');
        $code = $lazyLoader->generatorLazyProxy('HyperfLazy\\Foo\\', FooImplLazyLoad::class);
        self::assertEquals($proxyCode, $code);
    }

    public function testGeneratorLazyProxyClass()
    {
        $lazyLoader = LazyLoader::bootstrap(BASE_PATH);
        $proxyCode = file_get_contents(__DIR__ . '/Test.txt');
        $code = $lazyLoader->generatorLazyProxy('HyperfLazy\\Test\\', Test::class);
        self::assertEquals($proxyCode, $code);
    }
}
