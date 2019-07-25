<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Di\Aop;

class RewriteCollection
{
    const LEVEL_CLASS = 1;

    const LEVEL_METHOD = 2;

    /**
     * Which methods can be rewrite.
     * @var array
     */
    protected $methods = [];

    /**
     * Rewrite level.
     * @var int
     */
    protected $level = self::LEVEL_METHOD;

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

    public function has(string $aspect, ?string $method = null)
    {
        if ($method === null) {
            return isset($this->methods[$aspect]);
        }

        return isset($this->methods[$aspect][$method]);
    }

    /**
     * @param array|string $methods
     */
    public function add(string $aspect, $methods): self
    {
        $this->methods[$aspect] = array_unique(array_merge($this->methods[$aspect] ?? [], (array) $methods));
        return $this;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;
        return $this;
    }

    public function shouldRewrite(string $method): bool
    {
        if ($this->level === self::LEVEL_CLASS) {
            if (in_array($method, $this->shouldNotRewriteMethods)) {
                return false;
            }
            return true;
        }

        $methods = array_merge(...$this->methods);

        return in_array($method, $methods);
    }

    /**
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return array
     */
    public function getShouldNotRewriteMethods(): array
    {
        return $this->shouldNotRewriteMethods;
    }
}
