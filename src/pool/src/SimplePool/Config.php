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
     * @var string
     */
    public $name;

    /**
     * @var callable
     */
    public $callback;

    /**
     * @var array
     */
    public $option;

    public function __construct(string $name, callable $callback, array $option)
    {
        $this->name = $name;
        $this->callback = $callback;
        $this->option = $option;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Config
    {
        $this->name = $name;
        return $this;
    }

    public function getCallback(): callable
    {
        return $this->callback;
    }

    public function setCallback(callable $callback): Config
    {
        $this->callback = $callback;
        return $this;
    }

    public function getOption(): array
    {
        return $this->option;
    }

    public function setOption(array $option): Config
    {
        $this->option = $option;
        return $this;
    }
}
