<?php

use Hyperf\Framework\ApplicationContext;

if (! function_exists('make')) {
    function make(string $name, array $parameters = [])
    {
        return ApplicationContext::getContainer()->make($name, $parameters);
    }
}