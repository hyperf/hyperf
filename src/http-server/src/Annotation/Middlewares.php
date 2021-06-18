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

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"ALL"})
 */
#[Attribute]
class Middlewares extends AbstractAnnotation
{
    /**
     * @var array
     */
    public $middlewares = [];

    public function __construct(...$value)
    {
        if (count($value) > 1) {
            $middlewares = [];
            foreach ($value as $key => $middlewareName) {
                $middlewares['value'][] = new Middleware($middlewareName);
            }
            $value = $middlewares;
        }
        $this->bindMainProperty('middlewares', $value);
    }
}
