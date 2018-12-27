<?php

namespace Hyperf\Dispatcher;


abstract class AbstractDispatcher implements DispatcherInterface
{

    /**
     * @param array ...$params
     * @return mixed
     */
    public function dispatch(...$params)
    {
        return $this->handle(...$params);
    }

}