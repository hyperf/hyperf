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

namespace Hyperf\IDEHelper;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use ReflectionClass;

class Ast
{
    private Parser $parser;

    private PrettyPrinterAbstract $printer;

    private Reader $reader;

    public function __construct()
    {
        $parserFactory = new ParserFactory();
        $this->parser = $parserFactory->create(ParserFactory::ONLY_PHP7);
        $this->printer = new Standard();
        $this->reader = new AnnotationReader();
    }

    public function generate(ReflectionClass $reflection, string $code, array $visitors = []): string
    {
        $stmts = $this->parser->parse($code);

        $traverser = new NodeTraverser();
        $metadata = new Metadata($this->reader, $reflection);
        foreach ($visitors as $string) {
            $visitor = new $string($metadata);
            $traverser->addVisitor($visitor);
        }
        $modifiedStmts = $traverser->traverse($stmts);
        return $this->printer->prettyPrintFile($modifiedStmts);
    }
}
