<?php


namespace Hyperf\View\Engine;

use duncan3dc\Laravel\BladeInstance;

class BladeEngine implements EngineInterface
{
    public function render($template, $data, $config): string
    {
        $blade = new BladeInstance($config['view_path'], $config['cache_path']);

        return $blade->render($template, $data);
    }
}