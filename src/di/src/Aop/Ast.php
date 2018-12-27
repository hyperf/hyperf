<?php

namespace Hyperf\Di\Aop;

use App\Controllers\IndexController;
use Hyperf\Utils\Composer;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser as AstParserInterface;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;

class Ast
{

    /**
     * @var \PhpParser\Parser
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

    public function parse(string $code): ?array
    {
        return $this->astParser->parse($code);
    }

    public function proxy(string $className, string $proxyClassName)
    {
        $stmts = AstCollector::get($className, value(function () use ($className) {
            $code = $this->getCodeByClassName($className);
            return $stmts = $this->astParser->parse($code);
        }));
        $traverser = new NodeTraverser();
        // @TODO Allow user modify or replace node vistor.
        $traverser->addVisitor(new ProxyClassNameVistor($proxyClassName));
        $traverser->addVisitor(new ProxyCallVistor());
        $modifiedStmts = $traverser->traverse($stmts);
        $code = $this->printer->prettyPrintFile($modifiedStmts);
        return $code;
    }

    public function parseClassByStmts(array $stmts): string
    {
        $namespace = $className = '';
        foreach ($stmts as $stmt) {
            if ($stmt instanceof Namespace_) {
                $namespace = $stmt->name->toString();
                foreach ($stmt->stmts as $node) {
                    if ($node instanceof Class_) {
                        $className = $node->name->toString();
                        break;
                    }
                }
            }
        }
        return ($namespace && $className) ? $namespace . '\\' . $className : '';
    }

    private function getCodeByClassName(string $className)
    {
        $file = Composer::getLoader()->findFile($className);
        if (! $file) {
            return '';
        }
        return file_get_contents($file);
    }


}