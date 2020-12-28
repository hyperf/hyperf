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
namespace Hyperf\Phar\Ast;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;

class Ast
{
    /**
     * @var Parser
     */
    private $astParser;

    /**
     * @var PrettyPrinterAbstract
     */
    private $printer;

    public function __construct()
    {
        $parserFactory = new ParserFactory();
        $this->astParser = $parserFactory->create(ParserFactory::ONLY_PHP7);
        $this->printer = new Standard();
    }

    public function parse(string $code, array $visitors): string
    {
        $traverser = new NodeTraverser();
        foreach ($visitors as $visitor) {
            $traverser->addVisitor($visitor);
        }
        $stmts = $this->astParser->parse($code);
        $stmts = $traverser->traverse($stmts);
        array_push($stmts, $this->createReturn());
        return $this->printer->prettyPrintFile($stmts);
    }

    protected function createReturn(): Node\Stmt\Return_
    {
        $funcCall = new Node\Expr\FuncCall(new Node\Name('array_replace'));
        $funcCall->args = [
            new Node\Arg(new Node\Expr\Variable('result')),
            $this->createScanArg(),
        ];
        return new Node\Stmt\Return_($funcCall);
    }

    protected function createScanArg(): Node\Arg
    {
        $array = new Node\Expr\Array_();
        $array->items[] = new Node\Expr\ArrayItem(
            new Node\Expr\ConstFetch(new Node\Name('true')),
            new Node\Scalar\String_('scan_cacheable')
        );
        return new Node\Arg($array);
    }
}
