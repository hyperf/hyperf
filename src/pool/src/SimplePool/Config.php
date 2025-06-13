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

namespace Hyperf\Pool\SimplePool;

class Config
{
    /**
     * @var callable
     */
    protected $callback;

    public function __construct(protected string $name, callable $callback, protected array $option)
    {
        $this->callback = $callback;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getCallback(): callable
    {
        return $this->callback;
    }

    public function setCallback(callable $callback): static
    {
        $this->callback = $callback;
        return $this;
    }

    public function getOption(): array
    {
        return $this->option;
    }

    public function setOption(array $option): static
    {
        $this->option = $option;
        return $this;
    }
}
