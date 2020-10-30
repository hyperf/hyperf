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
namespace Hyperf\HttpServer\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;
use RuntimeException;

/**
 * @Annotation
 * @Target({"ALL"})
 */
class Middleware extends AbstractAnnotation
{
    public $middleware;

    /**
     * @var array
     */
    public $arguments = [];

    public function __construct($middleware = null,array $arguments = [])
    {
        parent::__construct();
        $this->bindMainProperty('middleware', ['value' => $middleware]);
        $this->bindMainProperty('arguments', ['value' => $arguments]);
    }

    /**
     * @param array $config
     * @return static[]
     */
    public static function parseConfig(array $config) :array
    {
        $middlewares = [];
        foreach ($config as $middleware) {
            if (is_string($middleware) && class_exists($middleware)) {
                $middlewares[] = new static($middleware);
            } else if (is_array($middleware) && array_values($middleware) === $middleware) {
                $middlewares[] = new static(array_shift($middleware),$middleware);
            } else {
                throw new RuntimeException('Invalid Middleware Configuration');
            }

        }
        return $middlewares;
    }

}
