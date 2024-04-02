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

namespace Hyperf\RpcClient\Proxy;

use InvalidArgumentException;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use ReflectionClass;

class Ast
{
    protected Parser $astParser;

    protected PrettyPrinterAbstract $printer;

    protected CodeLoader $codeLoader;

    public function __construct()
    {
        $parserFactory = new ParserFactory();
        $this->astParser = $parserFactory->create(ParserFactory::ONLY_PHP7);
        $this->printer = new Standard();
        $this->codeLoader = new CodeLoader();
    }

    public function proxy(string $className, string $proxyClassName)
    {
        if (! interface_exists($className)) {
            throw new InvalidArgumentException("'{$className}' should be an interface name");
        }
        if (str_contains($proxyClassName, '\\')) {
            $exploded = explode('\\', $proxyClassName);
            $proxyClassName = end($exploded);
        }

        $code = $this->codeLoader->getCodeByClassName($className);
        $stmts = $this->astParser->parse($code);

        $ref = new ReflectionClass($className);
        $parentStmts = [];
        foreach ($ref->getInterfaces() as $class => $reflection) {
            $parentStmts[] = $this->astParser->parse(
                $this->codeLoader->getCodeByClassName($class)
            );
        }

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new ProxyCallVisitor($proxyClassName, $parentStmts));
        $modifiedStmts = $traverser->traverse($stmts);
        return $this->printer->prettyPrintFile($modifiedStmts);
    }
}
