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

/**
 * @Annotation
 * @Target({"ALL"})
 */
class Middleware extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $middleware = '';

    public function __construct($value = null)
    {
        parent::__construct($value);
        $this->bindMainProperty('middleware', $value);
    }
}
