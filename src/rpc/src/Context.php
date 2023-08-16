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
namespace Hyperf\Rpc;

use Hyperf\Collection\Arr;
use Hyperf\Context\Context as ContextUtil;

class Context
{
    public function getData(): array
    {
        return ContextUtil::get($this->getContextKey(), []);
    }

    public function setData(array $data): void
    {
        ContextUtil::set($this->getContextKey(), $data);
    }

    public function get($key, $default = null)
    {
        return Arr::get($this->getData(), $key, $default);
    }

    public function set($key, $value): void
    {
        $data = $this->getData();
        $data[$key] = $value;
        ContextUtil::set($this->getContextKey(), $data);
    }

    public function clear(): void
    {
        ContextUtil::set($this->getContextKey(), []);
    }

    protected function getContextKey(): string
    {
        return static::class . '::DATA';
    }
}
