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

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

class Ast
{
    public static function parse(string $code, array $visitors): string
    {
        $parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $printer = new Standard();
        $traverser = new NodeTraverser();
        foreach ($visitors as $visitor) {
            $traverser->addVisitor($visitor);
        }
        $stmts = $parser->parse($code);
        $stmts = $traverser->traverse($stmts);
        return $printer->prettyPrintFile($stmts);
    }
}
