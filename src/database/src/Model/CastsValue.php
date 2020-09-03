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

use Hyperf\Contract\Synchronized;
use Hyperf\Utils\Contracts\Arrayable;

abstract class CastsValue implements Synchronized, Arrayable
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var array
     */
    protected $items;

    /**
     * @var bool
     */
    protected $isSynchronized;

    public function __construct(Model $model, $itmes = [])
    {
        $this->model = $model;
        $this->items = $itmes;
    }

    public function __get($name)
    {
        return $this->items[$name];
    }

    public function __set($name, $value)
    {
        $this->items[$name] = $value;
        $this->isSynchronized = false;
        $this->model->syncAttributes();
        $this->isSynchronized = true;
    }

    public function isSynchronized(): bool
    {
        return $this->isSynchronized;
    }

    public function toArray(): array
    {
        return $this->items;
    }
}
