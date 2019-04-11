<?php

namespace Hyperf\Http\Message\Router;

/**
 * Handler mapping interface
 */
interface HandlerMappingInterface
{
    /**
     * the handler of controller
     *
     * @param array ...$params
     *
     * @return array
     */
    public function getHandler(...$params);
}
