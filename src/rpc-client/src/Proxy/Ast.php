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

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;

class Ast
{
    /**
     * @var \PhpParser\Parser
     */
    protected $astParser;

    /**
     * @var PrettyPrinterAbstract
     */
    protected $printer;

    /**
     * @var CodeLoader
     */
    protected $codeLoader;

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
            throw new \InvalidArgumentException("'{$className}' should be an interface name");
        }
        if (strpos($proxyClassName, '\\') !== false) {
            $exploded = explode('\\', $proxyClassName);
            $proxyClassName = end($exploded);
        }

        $code = $this->codeLoader->getCodeByClassName($className);
        $stmts = $this->astParser->parse($code);
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new ProxyCallVisitor($proxyClassName));
        $modifiedStmts = $traverser->traverse($stmts);
        return $this->printer->prettyPrintFile($modifiedStmts);
    }
}
