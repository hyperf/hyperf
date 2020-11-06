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
namespace Hyperf\Di\LazyLoader;

use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Const_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassConst;

abstract class AbstractLazyProxyBuilder
{
    /**
     * The Builder instance.
     * @var mixed
     */
    public $builder;

    /**
     * The BuilderFactory.
     * @var BuilderFactory
     */
    public $factory;

    /**
     * Class Namespace.
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $proxyClassName;

    /**
     * @var string
     */
    protected $originalClassName;

    public function __construct()
    {
        $this->factory = new BuilderFactory();
        $this->builder = $this->factory;
    }

    abstract public function addClassRelationship(): AbstractLazyProxyBuilder;

    public function addClassBoilerplate(string $proxyClassName, string $originalClassName): AbstractLazyProxyBuilder
    {
        $namespace = join('\\', array_slice(explode('\\', $proxyClassName), 0, -1));
        $this->namespace = $namespace;
        $this->proxyClassName = $proxyClassName;
        $this->originalClassName = $originalClassName;
        $this->builder = $this->factory->class(class_basename($proxyClassName))
            ->addStmt(new ClassConst([new Const_('PROXY_TARGET', new String_($originalClassName))]))
            ->addStmt($this->factory->useTrait('\\Hyperf\\Di\\LazyLoader\\LazyProxyTrait'))
            ->setDocComment("/**
                              * Be careful: This is a lazy proxy, not the real {$originalClassName} from container.
                              *
                              * {@inheritdoc}
                              */");
        return $this;
    }

    public function addNodes(array $nodes): AbstractLazyProxyBuilder
    {
        foreach ($nodes as $stmt) {
            $this->builder = $this->builder->addStmt($stmt);
        }
        return $this;
    }

    public function getNode(): Node
    {
        if ($this->namespace) {
            return $this->factory
                ->namespace($this->namespace)
                ->addStmt($this->builder)
                ->getNode();
        }
        return $this->builder->getNode();
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getProxyClassName(): string
    {
        return $this->proxyClassName;
    }

    public function getOriginalClassName(): string
    {
        return $this->originalClassName;
    }
}
