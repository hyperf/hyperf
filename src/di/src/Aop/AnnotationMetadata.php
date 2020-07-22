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
namespace Hyperf\Di\Aop;

class AnnotationMetadata
{
    public $class = [];

    public $method = [];

    public function __construct(array $class, array $method)
    {
        $this->class = $class;
        $this->method = $method;
    }
}
