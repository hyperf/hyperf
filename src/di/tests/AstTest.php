<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Di;

use Hyperf\Di\Aop\Ast;
use HyperfTest\Di\Stub\AspectCollector;
use HyperfTest\Di\Stub\Ast\Bar2;
use HyperfTest\Di\Stub\Ast\Bar3;
use HyperfTest\Di\Stub\Ast\BarAspect;
use HyperfTest\Di\Stub\Ast\Foo;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AstTest extends TestCase
{
    public function testProxy()
    {
        $ast = new Ast();
        $proxyClass = Foo::class . 'Froxy';
        $code = $ast->proxy(Foo::class, $proxyClass);

        $this->assertEquals('<?php

declare (strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */
namespace HyperfTest\Di\Stub\Ast;

class FooFroxy extends Foo
{
    use \Hyperf\Di\Aop\ProxyTrait;
}', $code);
    }

    public function testParentMethods()
    {
        $ast = new Ast();
        $proxyClass = Bar2::class . 'Froxy';
        $code = $ast->proxy(Bar2::class, $proxyClass);

        $this->assertEquals('<?php

declare (strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */
namespace HyperfTest\Di\Stub\Ast;

class Bar2Froxy extends Bar2
{
    use \Hyperf\Di\Aop\ProxyTrait;
    public function __construct(int $id)
    {
        Bar::__construct($id);
    }
    public static function build()
    {
        return Bar::$items;
    }
}', $code);
    }

    public function testRewriteMethods()
    {
        $aspect = BarAspect::class;

        AspectCollector::setAround($aspect, [
            Bar3::class,
        ], []);

        $ast = new Ast();
        $proxyClass = Bar3::class . 'Froxy';
        $code = $ast->proxy(Bar3::class, $proxyClass);

        $this->assertEquals('<?php

declare (strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */
namespace HyperfTest\Di\Stub\Ast;

class Bar3Froxy extends Bar3
{
    use \Hyperf\Di\Aop\ProxyTrait;
    public function getId() : int
    {
        $__function__ = __FUNCTION__;
        $__method__ = __METHOD__;
        return self::__proxyCall(Bar3::class, __FUNCTION__, self::getParamsMap(Bar3::class, __FUNCTION__, func_get_args()), function () use($__function__, $__method__) {
            return parent::getId();
        });
    }
}', $code);
    }
}
