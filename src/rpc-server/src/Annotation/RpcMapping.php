<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\RpcServer\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class RpcMapping extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $path = '';

    public function __construct($value = null)
    {
        if (isset($value['path'])) {
            $this->path = $value['path'];
        }
        $this->bindMainProperty('path', $value);
    }
}
