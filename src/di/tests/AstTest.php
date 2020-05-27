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
namespace HyperfTest\Di;

use Hyperf\Di\Aop\Ast;
use Hyperf\Di\BetterReflectionManager;
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
    protected function tearDown()
    {
        BetterReflectionManager::clear();
    }

    public function testAstProxy()
    {
        BetterReflectionManager::initClassReflector([__DIR__ . '/Stub']);

        $ast = new Ast();
        $code = $ast->proxy(Foo::class);

        $this->assertEquals('<?php

declare (strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace HyperfTest\Di\Stub\Ast;

class Foo
{
    use \Hyperf\Di\Aop\ProxyTrait;
    use \Hyperf\Di\Aop\PropertyHandlerTrait;
    function __construct()
    {
        if (get_parent_class() && method_exists(parent::class, \'__construct\')) {
            parent::__construct(...func_get_args());
        }
        self::__handlePropertyHandler(__CLASS__);
    }
}', $code);
    }

    public function testParentMethods()
    {
        BetterReflectionManager::initClassReflector([__DIR__ . '/Stub']);

        $ast = new Ast();
        $code = $ast->proxy(Bar2::class);
        $this->assertEquals('<?php

declare (strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace HyperfTest\Di\Stub\Ast;

class Bar2 extends Bar
{
    use \Hyperf\Di\Aop\ProxyTrait;
    use \Hyperf\Di\Aop\PropertyHandlerTrait;
    public function __construct(int $id)
    {
        self::__handlePropertyHandler(__CLASS__);
        parent::__construct($id);
    }
    public static function build()
    {
        return parent::$items;
    }
}', $code);
    }

    public function testRewriteMethods()
    {
        BetterReflectionManager::initClassReflector([__DIR__ . '/Stub']);

        $aspect = BarAspect::class;

        AspectCollector::setAround($aspect, [
            Bar3::class,
        ], []);

        $ast = new Ast();
        $code = $ast->proxy(Bar3::class);

        $this->assertEquals('<?php

declare (strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace HyperfTest\Di\Stub\Ast;

class Bar3 extends Bar
{
    use \Hyperf\Di\Aop\ProxyTrait;
    use \Hyperf\Di\Aop\PropertyHandlerTrait;
    function __construct(int $id)
    {
        if (get_parent_class() && method_exists(parent::class, \'__construct\')) {
            parent::__construct(...func_get_args());
        }
        self::__handlePropertyHandler(__CLASS__);
    }
    public function getId() : int
    {
        return self::__proxyCall(__CLASS__, __FUNCTION__, self::__getParamsMap(__CLASS__, __FUNCTION__, func_get_args()), function () {
            return parent::getId();
        });
    }
}', $code);
    }
}
