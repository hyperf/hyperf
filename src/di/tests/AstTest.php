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

namespace HyperfTest\Di;

use Hyperf\Di\Aop\Ast;
use Hyperf\Di\ReflectionManager;
use HyperfTest\Di\Stub\AspectCollector;
use HyperfTest\Di\Stub\Ast\Abs;
use HyperfTest\Di\Stub\Ast\AbsAspect;
use HyperfTest\Di\Stub\Ast\Bar;
use HyperfTest\Di\Stub\Ast\Bar2;
use HyperfTest\Di\Stub\Ast\Bar3;
use HyperfTest\Di\Stub\Ast\Bar4;
use HyperfTest\Di\Stub\Ast\Bar5;
use HyperfTest\Di\Stub\Ast\BarAspect;
use HyperfTest\Di\Stub\Ast\BarInterface;
use HyperfTest\Di\Stub\Ast\Chi;
use HyperfTest\Di\Stub\Ast\Foo;
use HyperfTest\Di\Stub\Ast\FooConstruct;
use HyperfTest\Di\Stub\Ast\FooEnum;
use HyperfTest\Di\Stub\Ast\FooTrait;
use HyperfTest\Di\Stub\FooAspect;
use HyperfTest\Di\Stub\FooEnumStruct;
use HyperfTest\Di\Stub\Par2;
use HyperfTest\Di\Stub\PathStub;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class AstTest extends TestCase
{
    protected $license = '<?php

declare (strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */';

    protected function tearDown(): void
    {
        ReflectionManager::clear();
    }

    public function testAstProxy()
    {
        $ast = new Ast();
        $code = $ast->proxy(Foo::class);

        $this->assertEquals($this->license . '
namespace HyperfTest\Di\Stub\Ast;

class Foo
{
    use \Hyperf\Di\Aop\ProxyTrait;
    use \Hyperf\Di\Aop\PropertyHandlerTrait;
    function __construct()
    {
        $this->__handlePropertyHandler(__CLASS__);
    }
}', $code);
    }

    public function testMagicConstDirAndFile()
    {
        $ast = new Ast();
        $code = $ast->proxy(PathStub::class);
        $path = (new PathStub())->file();
        $dir = (new PathStub())->dir();

        $this->assertSame($this->license . '
namespace HyperfTest\Di\Stub;

class PathStub
{
    use \Hyperf\Di\Aop\ProxyTrait;
    use \Hyperf\Di\Aop\PropertyHandlerTrait;
    function __construct()
    {
        $this->__handlePropertyHandler(__CLASS__);
    }
    public function file() : string
    {
        return \'' . $path . '\';
    }
    public function dir() : string
    {
        return \'' . $dir . '\';
    }
}', $code);
    }

    public function testParentWith()
    {
        $ast = new Ast();
        $code = $ast->proxy(Par2::class);

        $this->assertSame($this->license . '
namespace HyperfTest\Di\Stub;

class Par2 extends Par
{
    use \Hyperf\Di\Aop\ProxyTrait;
    use \Hyperf\Di\Aop\PropertyHandlerTrait;
    function __construct(?\HyperfTest\Di\Stub\Foo $foo)
    {
        if (method_exists(parent::class, \'__construct\')) {
            parent::__construct(...func_get_args());
        }
        $this->__handlePropertyHandler(__CLASS__);
    }
}', $code);
    }

    public function testAstProxyForEnum()
    {
        $ast = new Ast();
        $code = $ast->proxy(FooEnumStruct::class);

        $this->assertEquals($this->license . '
namespace HyperfTest\Di\Stub;

class FooEnumStruct
{
    use \Hyperf\Di\Aop\ProxyTrait;
    use \Hyperf\Di\Aop\PropertyHandlerTrait;
    public function __construct(public FooEnum $enum = FooEnum::DEFAULT)
    {
        $this->__handlePropertyHandler(__CLASS__);
    }
}', $code);
    }

    public function testAbstractMethod()
    {
        $aspect = AbsAspect::class;
        AspectCollector::setAround($aspect, [
            Chi::class,
            Abs::class,
        ], []);

        $ast = new Ast();
        $code = $ast->proxy(Abs::class);

        $this->assertSame($this->license . "
namespace HyperfTest\\Di\\Stub\\Ast;

abstract class Abs
{
    use \\Hyperf\\Di\\Aop\\ProxyTrait;
    use \\Hyperf\\Di\\Aop\\PropertyHandlerTrait;
    function __construct()
    {
        \$this->__handlePropertyHandler(__CLASS__);
    }
    public function abs() : string
    {
        \$__function__ = __FUNCTION__;
        \$__method__ = __METHOD__;
        return self::__proxyCall(__CLASS__, __FUNCTION__, ['keys' => []], function () use(\$__function__, \$__method__) {
            return 'abs';
        });
    }
    public abstract function absabs() : string;
}", $code);

        $code = $ast->proxy(Chi::class);
        $this->assertSame($this->license . '
namespace HyperfTest\Di\Stub\Ast;

class Chi extends Abs
{
    use \Hyperf\Di\Aop\ProxyTrait;
    use \Hyperf\Di\Aop\PropertyHandlerTrait;
    function __construct()
    {
        if (method_exists(parent::class, \'__construct\')) {
            parent::__construct(...func_get_args());
        }
        $this->__handlePropertyHandler(__CLASS__);
    }
    public function absabs() : string
    {
        $__function__ = __FUNCTION__;
        $__method__ = __METHOD__;
        return self::__proxyCall(__CLASS__, __FUNCTION__, [\'keys\' => []], function () use($__function__, $__method__) {
            return \'chi\';
        });
    }
}', $code);
    }

    public function testParentMethods()
    {
        $ast = new Ast();
        $code = $ast->proxy(Bar2::class);
        $this->assertEquals($this->license . '
namespace HyperfTest\Di\Stub\Ast;

class Bar2 extends Bar
{
    use \Hyperf\Di\Aop\ProxyTrait;
    use \Hyperf\Di\Aop\PropertyHandlerTrait;
    public function __construct(int $id)
    {
        $this->__handlePropertyHandler(__CLASS__);
        parent::__construct($id);
    }
    public static function build()
    {
        return parent::$items;
    }
}', $code);
    }

    public function testParentConstructor()
    {
        $ast = new Ast();
        $code = $ast->proxy(Bar5::class);
        $this->assertEquals($this->license . '
namespace HyperfTest\Di\Stub\Ast;

class Bar5
{
    use \Hyperf\Di\Aop\ProxyTrait;
    use \Hyperf\Di\Aop\PropertyHandlerTrait;
    public function getBar() : Bar
    {
        return new class extends Bar
        {
            public function __construct()
            {
                $this->__handlePropertyHandler(__CLASS__);
                $this->id = 9501;
            }
        };
    }
}', $code);
    }

    public function testMagicMethods()
    {
        $aspect = BarAspect::class;

        AspectCollector::setAround($aspect, [
            Bar4::class . '::toRewriteMethodString1',
            Bar4::class . '::toRewriteMethodString2',
            Bar4::class . '::toRewriteMethodString3',
            Bar4::class . '::toRewriteMethodString4',
        ], []);

        $ast = new Ast();
        $code = $ast->proxy(Bar4::class);
        $this->assertEquals($this->license . '
namespace HyperfTest\Di\Stub\Ast;

class Bar4
{
    use \Hyperf\Di\Aop\ProxyTrait;
    use \Hyperf\Di\Aop\PropertyHandlerTrait;
    function __construct()
    {
        $this->__handlePropertyHandler(__CLASS__);
    }
    public function toMethodString() : string
    {
        return __METHOD__;
    }
    /**
     * To test method parameters (with type declaration in use).
     */
    public function toRewriteMethodString1(int $count) : string
    {
        $__function__ = __FUNCTION__;
        $__method__ = __METHOD__;
        return self::__proxyCall(__CLASS__, __FUNCTION__, [\'order\' => [\'count\'], \'keys\' => compact([\'count\']), \'variadic\' => \'\'], function (int $count) use($__function__, $__method__) {
            return $__method__;
        });
    }
    /**
     * To test passing by references.
     */
    public function toRewriteMethodString2(int &$count) : string
    {
        $__function__ = __FUNCTION__;
        $__method__ = __METHOD__;
        return self::__proxyCall(__CLASS__, __FUNCTION__, [\'order\' => [\'count\'], \'keys\' => compact([\'count\']), \'variadic\' => \'\'], function (int &$count) use($__function__, $__method__) {
            return $__method__;
        });
    }
    /**
     * To test variadic parameters (without type declaration).
     */
    public function toRewriteMethodString3(...$params) : string
    {
        $__function__ = __FUNCTION__;
        $__method__ = __METHOD__;
        return self::__proxyCall(__CLASS__, __FUNCTION__, [\'order\' => [\'params\'], \'keys\' => compact([\'params\']), \'variadic\' => \'params\'], function (...$params) use($__function__, $__method__) {
            return $__method__;
        });
    }
    /**
     * To test variadic parameters with type declaration.
     */
    public function toRewriteMethodString4(int &$count, string ...$params) : string
    {
        $__function__ = __FUNCTION__;
        $__method__ = __METHOD__;
        return self::__proxyCall(__CLASS__, __FUNCTION__, [\'order\' => [\'count\', \'params\'], \'keys\' => compact([\'count\', \'params\']), \'variadic\' => \'params\'], function (int &$count, string ...$params) use($__function__, $__method__) {
            return $__method__;
        });
    }
}', $code);
    }

    public function testRewriteMethods()
    {
        $aspect = BarAspect::class;

        AspectCollector::setAround($aspect, [
            Bar3::class,
            FooTrait::class,
            BarInterface::class,
        ], []);

        $ast = new Ast();
        $code = $ast->proxy(Bar3::class);

        $this->assertEquals($this->license . '
namespace HyperfTest\Di\Stub\Ast;

class Bar3 extends Bar
{
    use \Hyperf\Di\Aop\ProxyTrait;
    use \Hyperf\Di\Aop\PropertyHandlerTrait;
    function __construct(int $id)
    {
        if (method_exists(parent::class, \'__construct\')) {
            parent::__construct(...func_get_args());
        }
        $this->__handlePropertyHandler(__CLASS__);
    }
    public function getId() : int
    {
        $__function__ = __FUNCTION__;
        $__method__ = __METHOD__;
        return self::__proxyCall(__CLASS__, __FUNCTION__, [\'keys\' => []], function () use($__function__, $__method__) {
            return parent::getId();
        });
    }
}', $code);

        $code = $ast->proxy(FooTrait::class);
        $this->assertSame($this->license . '
namespace HyperfTest\Di\Stub\Ast;

trait FooTrait
{
    use \Hyperf\Di\Aop\ProxyTrait;
    public function getString() : string
    {
        $__function__ = __FUNCTION__;
        $__method__ = __METHOD__;
        return self::__proxyCall(__TRAIT__, __FUNCTION__, [\'keys\' => []], function () use($__function__, $__method__) {
            return uniqid();
        });
    }
}', $code);

        $code = $ast->proxy(BarInterface::class);
        $this->assertSame($this->license . '
namespace HyperfTest\Di\Stub\Ast;

interface BarInterface
{
    public function toArray() : array;
}', $code);
    }

    public function testRewriteConstructor()
    {
        $aspect = FooAspect::class;

        AspectCollector::setAround($aspect, [
            FooConstruct::class . '::__construct',
        ], []);

        $ast = new Ast();
        $code = $ast->proxy(FooConstruct::class);

        $this->assertEquals($this->license . '
namespace HyperfTest\Di\Stub\Ast;

class FooConstruct
{
    use \Hyperf\Di\Aop\ProxyTrait;
    use \Hyperf\Di\Aop\PropertyHandlerTrait;
    public function __construct(public readonly string $name, protected readonly int $age = 18, private ?int $id = null)
    {
        $__function__ = __FUNCTION__;
        $__method__ = __METHOD__;
        return self::__proxyCall(__CLASS__, __FUNCTION__, [\'order\' => [\'name\', \'age\', \'id\'], \'keys\' => compact([\'name\', \'age\', \'id\']), \'variadic\' => \'\'], function (string $name, int $age = 18, ?int $id = null) use($__function__, $__method__) {
            $this->__handlePropertyHandler(__CLASS__);
        });
    }
}', $code);
    }

    public function testParseClassByStmtsMethods()
    {
        $parser = new Ast();
        $testCases = [
            [
                'class' => Bar::class,
                'expected' => 'HyperfTest\Di\Stub\Ast\Bar',
            ],
            [
                'class' => FooTrait::class,
                'expected' => 'HyperfTest\Di\Stub\Ast\FooTrait',
            ],
            [
                'class' => BarInterface::class,
                'expected' => 'HyperfTest\Di\Stub\Ast\BarInterface',
            ],
            [
                'class' => FooEnum::class,
                'expected' => 'HyperfTest\Di\Stub\Ast\FooEnum',
            ],
        ];

        foreach ($testCases as $testCase) {
            $reflector = new ReflectionClass($testCase['class']);
            $fileName = $reflector->getFileName();
            $stmts = $parser->parse(file_get_contents($fileName));
            $this->assertEquals($testCase['expected'], $parser->parseClassByStmts($stmts));
        }
    }
}
