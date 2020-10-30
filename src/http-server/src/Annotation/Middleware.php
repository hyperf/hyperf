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
    /**
     * @var string
     */
    public $middleware;

    /**
     * @var array
     */
    public $arguments = [];

    public function __construct($value = null)
    {
        parent::__construct($value);
        $this->bindMainProperty('middleware',$value);
        if(is_array($value)) {
            if (isset($value['middleware'])) {
                $this->middleware = $value['middleware'];
            }
            if (isset($value['arguments'])) {
                $this->arguments = $value['arguments'];
            }
        }
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
                $middlewares[] = new static(compact('middleware'));
            } else if (is_array($middleware) && array_values($middleware) === $middleware) {
                $middlewares[] = new static(['middleware' => array_shift($middleware),'arguments' => $middleware]);
            } else {
                throw new RuntimeException('Invalid Middleware Configuration');
            }

        }
        return $middlewares;
    }
}
