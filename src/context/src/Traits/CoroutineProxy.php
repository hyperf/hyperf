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

namespace Hyperf\Context\Traits;

use Hyperf\Context\Context;
use RuntimeException;

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
            throw new RuntimeException(sprintf('Missing $proxyKey property in %s.', $this::class));
        }
        return Context::get($this->proxyKey);
    }
}
