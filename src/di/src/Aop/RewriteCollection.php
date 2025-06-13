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
    public const CLASS_LEVEL = 1;

    public const METHOD_LEVEL = 2;

    /**
     * Which methods can be rewritten.
     */
    protected array $methods = [];

    /**
     * Method pattern.
     */
    protected array $pattern = [];

    /**
     * Rewrite level.
     */
    protected int $level = self::METHOD_LEVEL;

    protected array $shouldNotRewriteMethods = [
        '__construct',
    ];

    public function __construct(protected string $class)
    {
    }

    /**
     * @param string|string[] $methods
     */
    public function add($methods): self
    {
        $methods = (array) $methods;
        foreach ($methods as $method) {
            if (! str_contains($method, '*')) {
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
