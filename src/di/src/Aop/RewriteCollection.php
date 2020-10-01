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

class RewriteCollection
{
    const CLASS_LEVEL = 1;

    const METHOD_LEVEL = 2;

    /**
     * Which methods can be rewrite.
     * @var array
     */
    protected $methods = [];

    /**
     * Method pattern.
     * @var array
     */
    protected $pattern = [];

    /**
     * Rewrite level.
     * @var int
     */
    protected $level = self::METHOD_LEVEL;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var array
     */
    protected $shouldNotRewriteMethods = [
        '__construct',
    ];

    public function __construct(string $class)
    {
        $this->class = $class;
    }

    /**
     * @param array|string $methods
     */
    public function add($methods): self
    {
        $methods = (array) $methods;
        foreach ($methods as $method) {
            if (strpos($method, '*') === false) {
                $this->methods[] = $method;
            } else {
                $preg = str_replace(['*', '\\'], ['.*', '\\\\'], $method);
                $this->pattern[] = "/^{$preg}$/";
            }
        }

        return $this;
    }

    public function shouldRewrite(string $method): bool
    {
        if ($this->level === self::CLASS_LEVEL) {
            if (in_array($method, $this->shouldNotRewriteMethods)) {
                return false;
            }
            return true;
        }

        if (in_array($method, $this->methods)) {
            return true;
        }

        foreach ($this->pattern as $pattern) {
            if (preg_match($pattern, $method)) {
                return true;
            }
        }

        return false;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;
        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Return the methods that should not rewrite.
     */
    public function getShouldNotRewriteMethods(): array
    {
        return $this->shouldNotRewriteMethods;
    }
}
