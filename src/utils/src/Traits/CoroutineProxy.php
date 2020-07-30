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
namespace Hyperf\Utils\Traits;

use Hyperf\Utils\Context;

trait CoroutineProxy
{
    public function __call($name, $arguments)
    {
        $target = $this->getTargetObject();
        return $target->{$name}(...$arguments);
    }

    public function __get($name)
    {
        $target = $this->getTargetObject();
        return $target->{$name};
    }

    public function __set($name, $value)
    {
        $target = $this->getTargetObject();
        return $target->{$name} = $value;
    }

    protected function getTargetObject()
    {
        if (! isset($this->proxyKey)) {
            throw new \RuntimeException('$proxyKey property of class missing.');
        }
        return Context::get($this->proxyKey);
    }
}
