<?php

namespace Hyperf\Utils\Traits;


trait CoroutineProxy
{

    public function __call($name, $arguments)
    {
        $target = $this->getTargetObject();
        return $target->$name(...$arguments);
    }

    public function __get($name)
    {
        $target = $this->getTargetObject();
        return $target->$name;
    }

    public function __set($name, $value)
    {
        $target = $this->getTargetObject();
        return $target->$name = $value;
    }

    protected function getTargetObject()
    {
        if (! isset($this->proxyKey)) {
            throw new \RuntimeException('$proxyKey property of class missing.');
        }
        return Context::get($this->proxyKey);
    }


}