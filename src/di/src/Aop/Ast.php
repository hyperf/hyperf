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

namespace Hyperf\Di\Aop;

use Hyperf\Support\Composer;
use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;

class Ast
{
    private Parser $astParser;

    private PrettyPrinterAbstract $printer;

    public function __construct()
    {
        $parserFactory = new ParserFactory();
        $this->astParser = $parserFactory->createForNewestSupportedVersion();
        $this->printer = new Standard();
    }

    public function parse(string $code): ?array
    {
        return $this->astParser->parse($code);
    }

    public function proxy(string $className)
    {
        $code = $this->getCodeByClassName($className);
        $stmts = $this->astParser->parse($code);
        $traverser = new NodeTraverser();
        $visitorMetadata = new VisitorMetadata($className);
        // User could modify or replace the node visitors by Hyperf\Di\Aop\AstVisitorRegistry.
        $queue = clone AstVisitorRegistry::getQueue();
        foreach ($queue as $string) {
            $visitor = new $string($visitorMetadata);
            $traverser->addVisitor($visitor);
        }
        $modifiedStmts = $traverser->traverse($stmts);
        return $this->printer->prettyPrintFile($modifiedStmts);
    }

    /**
     * Build the line map between the proxy code and the original code.
     *
     * The map is used to convert the file and line of an exception
     * thrown in the proxy file back to the original class file,
     * so that the developer can locate the problem in the original code.
     *
     * @return array<string, mixed> ['file' => string, 'ranges' => int[][], 'methods' => array]
     */
    public function buildLineMap(string $className, string $proxyCode): array
    {
        $originStmts = $this->astParser->parse($this->getCodeByClassName($className));
        $proxyStmts = $this->astParser->parse($proxyCode);
        if (! $originStmts || ! $proxyStmts) {
            return [];
        }

        $originMethods = $this->collectMethods($originStmts, $className);
        $proxyMethods = $this->collectMethods($proxyStmts, $className);
        if (! $originMethods || ! $proxyMethods) {
            return [];
        }

        $file = Composer::getLoader()->findFile($className);
        $map = [
            'file' => $file ? realpath($file) : $file,
            'ranges' => [],
            'methods' => [],
        ];

        foreach ($proxyMethods as $name => $proxyMethod) {
            if (! isset($originMethods[$name])) {
                continue;
            }
            $originMethod = $originMethods[$name];
            $originBody = $originMethod->stmts ?? [];
            $proxyBody = $proxyMethod->stmts ?? [];

            // The rewritten method wraps the original body into a closure
            // which is passed to `self::__proxyCall(...)`, extract it first.
            if ($this->isProxyCallMethod($proxyBody)) {
                $proxyBody = $this->extractProxyCallClosureStmts($proxyBody);
            }
            // The statements of the original method keep their order and
            // structure in the proxy code, so align them one by one.
            // The statements injected by the visitors, e.g. the
            // `$this->__handlePropertyHandler(__CLASS__);` statement
            // prepended to the constructor, should be skipped.
            $i = 0;
            $j = 0;
            $originCount = count($originBody);
            $proxyCount = count($proxyBody);
            while ($i < $originCount && $j < $proxyCount) {
                $proxy = $proxyBody[$j];
                if ($this->isInjectedStatement($proxy)) {
                    ++$j;
                    continue;
                }
                $origin = $originBody[$i];
                if (! $this->areStatementsAlignable($origin, $proxy)) {
                    break;
                }
                $this->alignStatements($origin, $proxy, $map['ranges']);
                ++$i;
                ++$j;
            }

            // Method ranges are kept separately as a trace-only fallback.
            // Exception locations use statement ranges exclusively to avoid
            // guessing a source line when a visitor changed the structure.
            $map['methods'][$name] = [
                $proxyMethod->getStartLine(),
                $proxyMethod->getEndLine(),
                $originMethod->getStartLine(),
                $originMethod->getEndLine(),
            ];
        }

        return $map;
    }

    public function parseClassByStmts(array $stmts): string
    {
        $namespace = $className = '';
        foreach ($stmts as $stmt) {
            if ($stmt instanceof Namespace_ && $stmt->name) {
                $namespace = $stmt->name->toString();
                foreach ($stmt->stmts as $node) {
                    if (($node instanceof ClassLike) && $node->name) {
                        $className = $node->name->toString();
                        break;
                    }
                }
            }
        }
        return ($namespace && $className) ? $namespace . '\\' . $className : '';
    }

    /**
     * Collect the class methods of the current namespace by their names.
     */
    private function collectMethods(array $stmts, string $className): array
    {
        $methods = [];
        foreach ($stmts as $stmt) {
            $namespace = '';
            $members = [$stmt];
            if ($stmt instanceof Namespace_) {
                $namespace = $stmt->name?->toString() ?? '';
                $members = $stmt->stmts;
            }
            foreach ($members as $class) {
                if (! $class instanceof ClassLike) {
                    continue;
                }
                $name = $class->name?->toString();
                if ($name === null || ltrim($namespace . '\\' . $name, '\\') !== ltrim($className, '\\')) {
                    continue;
                }
                foreach ($class->stmts as $member) {
                    if ($member instanceof ClassMethod && $member->name) {
                        $methods[$member->name->toString()] = $member;
                    }
                }
            }
        }
        return $methods;
    }

    /**
     * Align the node between the origin code and the proxy code, and
     * record the line range pair. The nested nodes, e.g. the branches
     * of an `if` statement, the statements of a `closure` or the
     * statements of a `try` block, are aligned recursively so that
     * the line translation inside the compound statements is precise.
     */
    private function alignStatements(Node $origin, Node $proxy, array &$ranges): void
    {
        if (! $this->areStatementsAlignable($origin, $proxy)) {
            return;
        }
        $ranges[] = [
            $proxy->getStartLine(),
            $proxy->getEndLine(),
            $origin->getStartLine(),
            $origin->getEndLine(),
        ];
        $originChildren = $this->childStatements($origin);
        $proxyChildren = $this->childStatements($proxy);
        // If the structures are not isomorphic, e.g. the statements
        // injected by the visitors, fall back to the compound range
        // which is already recorded above.
        if (count($originChildren) !== count($proxyChildren)) {
            return;
        }
        foreach ($originChildren as $i => $child) {
            $this->alignStatements($child, $proxyChildren[$i], $ranges);
        }
    }

    /**
     * A visitor may inject arbitrary statements. Only align nodes whose
     * statement and top-level expression shapes still match.
     */
    private function areStatementsAlignable(Node $origin, Node $proxy): bool
    {
        if ($origin::class !== $proxy::class) {
            return false;
        }
        if ($origin instanceof Expression && $proxy instanceof Expression) {
            return $origin->expr::class === $proxy->expr::class;
        }
        if ($origin instanceof Return_ && $proxy instanceof Return_) {
            return $origin->expr?->getType() === $proxy->expr?->getType();
        }
        return true;
    }

    /**
     * Collect the child statements or closures of a node, including
     * the statements of the compound statements such as `if`, `for`,
     * `foreach`, `try`, `closure`, `elseif` and `catch`.
     *
     * @return array<Closure|Stmt>
     */
    private function childStatements(Node $node): array
    {
        $children = [];
        foreach ($node->getSubNodeNames() as $name) {
            $value = $node->{$name};
            if (is_array($value)) {
                foreach ($value as $item) {
                    if ($item instanceof Stmt || $item instanceof Closure) {
                        $children[] = $item;
                    }
                }
            } elseif ($value instanceof Stmt || $value instanceof Closure) {
                $children[] = $value;
            }
        }
        return $children;
    }

    /**
     * Whether the statement is injected by the visitors, e.g. the
     * `$this->__handlePropertyHandler(__CLASS__);` statement prepended
     * to the constructor by the PropertyHandlerVisitor.
     */
    private function isInjectedStatement(Stmt $stmt): bool
    {
        if (! $stmt instanceof Expression || ! $stmt->expr instanceof MethodCall) {
            return false;
        }
        return $stmt->expr->var instanceof Variable
            && $stmt->expr->var->name === 'this'
            && $stmt->expr->name instanceof Identifier
            && $stmt->expr->name->toString() === '__handlePropertyHandler';
    }

    /**
     * Whether the method is rewritten into a `self::__proxyCall(...)` call,
     * which means its original body is wrapped into the closure argument.
     */
    private function isProxyCallMethod(array $stmts): bool
    {
        $last = end($stmts);
        if ($last instanceof Return_) {
            return $last->expr instanceof StaticCall && $this->isProxyCallName($last->expr->name);
        }
        if ($last instanceof Expression) {
            return $last->expr instanceof StaticCall && $this->isProxyCallName($last->expr->name);
        }
        return false;
    }

    private function isProxyCallName(Identifier $name): bool
    {
        return $name->toString() === '__proxyCall';
    }

    /**
     * Extract the original method body from the closure argument of
     * the `self::__proxyCall(...)` call.
     *
     * @return array<Stmt>
     */
    private function extractProxyCallClosureStmts(array $stmts): array
    {
        $last = end($stmts);
        $expr = $last instanceof Return_ || $last instanceof Expression ? $last->expr : null;
        if (! $expr instanceof StaticCall) {
            return [];
        }
        // The 4th argument of `__proxyCall()` is the closure which
        // wrapped the original method body.
        $closure = $expr->args[3]->value ?? null;
        if (! $closure instanceof Closure) {
            return [];
        }
        return $closure->stmts ?? [];
    }

    private function getCodeByClassName(string $className): string
    {
        $file = Composer::getLoader()->findFile($className);
        if (! $file) {
            return '';
        }
        return file_get_contents($file);
    }
}
