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

namespace Hyperf\Database\Model;

use Hyperf\Contract\Arrayable;
use Hyperf\Contract\Synchronized;

abstract class CastsValue implements Synchronized, Arrayable
{
    protected array $items = [];

    protected bool $isSynchronized = false;

    public function __construct(protected Model $model, array $items = [])
    {
        $this->items = array_merge($this->items, $items);
    }

    public function __get($name)
    {
        return $this->items[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->items[$name] = $value;
        $this->syncAttributes();
    }

    public function __isset($name)
    {
        return isset($this->items[$name]);
    }

    public function __unset($name)
    {
        unset($this->items[$name]);
        $this->syncAttributes();
    }

    public function isSynchronized(): bool
    {
        return $this->isSynchronized;
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function syncAttributes(): void
    {
        $this->isSynchronized = false;
        $this->model->syncAttributes();
        $this->isSynchronized = true;
    }
}
