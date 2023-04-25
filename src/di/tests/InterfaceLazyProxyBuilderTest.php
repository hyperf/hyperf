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

use Hyperf\CodeParser\PhpParser;
use Hyperf\Di\LazyLoader\InterfaceLazyProxyBuilder;
use Hyperf\Di\LazyLoader\PublicMethodVisitor;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class InterfaceLazyProxyBuilderTest extends TestCase
{
    public function testVisitInterface()
    {
        $code = <<<'CODETEMPLATE'
<?php
namespace foo;

use bar\ConfigInterface;
interface foo {
	abstract public function hope(bool $a): int;
	
	public function it(ConfigInterface $a): void;

	public function works(bool $a, float $b = 1): int;
}
CODETEMPLATE;
        $expected = <<<'CODETEMPLATE'
<?php

namespace Lazy;

/**
 * Be careful: This is a lazy proxy, not the real App\SomeInterface from container.
 *
 * {@inheritdoc}
 */
class SomeClass implements \App\SomeInterface
{
    use \Hyperf\Di\LazyLoader\LazyProxyTrait;
    const PROXY_TARGET = 'App\\SomeInterface';
    public function hope(bool $a) : int
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }
    public function it(\bar\ConfigInterface $a) : void
    {
        $this->__call(__FUNCTION__, func_get_args());
    }
    public function works(bool $a, float $b = 1) : int
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }
}
CODETEMPLATE;

        $builder = new InterfaceLazyProxyBuilder();
        $builder->addClassBoilerplate('Lazy\\SomeClass', 'App\\SomeInterface');
        $builder->addClassRelationship();
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse($code);
        $traverser = new NodeTraverser();
        $visitor = new PublicMethodVisitor(...$this->getStmt($ast));
        $nameResolver = new NameResolver();
        $traverser->addVisitor($nameResolver);
        $traverser->addVisitor($visitor);
        $ast = $traverser->traverse($ast);
        $builder->addNodes($visitor->nodes);
        $prettyPrinter = new Standard();
        $stmts = [$builder->getNode()];
        $newCode = $prettyPrinter->prettyPrintFile($stmts);
        $this->assertEquals($expected, $newCode);
    }

    private function getStmt($ast)
    {
        $stmts = PhpParser::getInstance()->getAllMethodsFromStmts($ast);
        return [$stmts, 'foo\\foo'];
    }
}
