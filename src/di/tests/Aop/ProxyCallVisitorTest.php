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

namespace HyperfTest\Di\Aop;

use Hyperf\Di\Aop\Ast;
use Hyperf\Di\Aop\ProxyCallVisitor;
use Hyperf\Di\Aop\VisitorMetadata;
use HyperfTest\Di\Stub\AspectCollector;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
/**
 * @internal
 * @coversNothing
 */
class ProxyCallVisitorTest extends TestCase
{
    protected function tearDown(): void
    {
        AspectCollector::clear();
    }

    public function testShouldRewrite()
    {
        $code = <<<'CODETEMPLATE'
<?php 
abstract class SomeClass
{
    abstract protected function foo();

    protected function bar()
    {
    }
}
CODETEMPLATE;

        $ast = new Ast();
        $stmts = $ast->parse($code)[0];

        $aspect = 'App\Aspect\DebugAspect';
        AspectCollector::setAround($aspect, [
            'SomeClass',
        ], []);

        $proxyCallVisitor = new ProxyCallVisitor(new VisitorMetadata('SomeClass'));

        $reflectionMethod = new ReflectionMethod($proxyCallVisitor, 'shouldRewrite');
        $this->assertFalse($reflectionMethod->invoke($proxyCallVisitor, $stmts->stmts[0]));
        $this->assertTrue($reflectionMethod->invoke($proxyCallVisitor, $stmts->stmts[1]));
    }

    public function testInterfaceShouldNotRewrite()
    {
        $aspect = 'App\Aspect\DebugAspect';
        AspectCollector::setAround($aspect, [
            'SomeClass',
        ], []);

        $visitorMetadata = new VisitorMetadata('SomeClass');
        $proxyCallVisitor = new ProxyCallVisitor($visitorMetadata);

        $reflectionMethod = new ReflectionMethod($proxyCallVisitor, 'shouldRewrite');
        $this->assertTrue($reflectionMethod->invoke($proxyCallVisitor, new ClassMethod('foo')));

        $visitorMetadata->classLike = Node\Stmt\Interface_::class;
        $this->assertFalse($reflectionMethod->invoke($proxyCallVisitor, new ClassMethod('foo')));
    }
}
