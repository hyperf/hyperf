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

use Hyperf\Di\LazyLoader\PublicMethodVisitor;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class PublicMethodVistorTest extends TestCase
{
    public function testVisitInterface()
    {
        $code = <<<'CODETEMPLATE'
<?php
namespace foo;

use bar\ConfigInterface;
interface foo {
	public function hope(bool $a): int;
	public function it(ConfigInterface $a): void;
	public function works(bool $a, float $b = 1): int;
}
CODETEMPLATE;
        $expected = <<<'CODETEMPLATE'
<?php

public function hope(bool $a) : int
{
    return $this->__call(__FUNCTION__, func_get_args());
}
public function it(ConfigInterface $a) : void
{
    return $this->__call(__FUNCTION__, func_get_args());
}
public function works(bool $a, float $b = 1) : int
{
    return $this->__call(__FUNCTION__, func_get_args());
}
CODETEMPLATE;
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse($code);
        $traverser = new NodeTraverser();
        $visitor = new PublicMethodVisitor();
        $traverser->addVisitor($visitor);
        $ast = $traverser->traverse($ast);
        $prettyPrinter = new \PhpParser\PrettyPrinter\Standard();
        $newCode = $prettyPrinter->prettyPrintFile($visitor->nodes);
        $this->assertEquals($expected, $newCode);
    }

    public function testVisitClass()
    {
        $code = <<<'CODETEMPLATE'
<?php
namespace foo;

use bar\ConfigInterface;
class foo {
	abstract public function hope(bool $a): int;

	public function it(ConfigInterface $a): void{
		sleep(1);
	}
	public function works(bool $a, float $b = 1): int{
		return self::works(false);
	}
}
CODETEMPLATE;
        $expected = <<<'CODETEMPLATE'
<?php

public function hope(bool $a) : int
{
    return $this->__call(__FUNCTION__, func_get_args());
}
public function it(ConfigInterface $a) : void
{
    return $this->__call(__FUNCTION__, func_get_args());
}
public function works(bool $a, float $b = 1) : int
{
    return $this->__call(__FUNCTION__, func_get_args());
}
CODETEMPLATE;
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse($code);
        $traverser = new NodeTraverser();
        $visitor = new PublicMethodVisitor();
        $traverser->addVisitor($visitor);
        $ast = $traverser->traverse($ast);
        $prettyPrinter = new \PhpParser\PrettyPrinter\Standard();
        $newCode = $prettyPrinter->prettyPrintFile($visitor->nodes);
        $this->assertEquals($expected, $newCode);
        $this->assertEquals(3, count($visitor->nodes));
    }
}
