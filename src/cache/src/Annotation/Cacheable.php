<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Cache\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"ALL"})
 */
class Cacheable extends AbstractAnnotation
{
    /**
     * @var array
     */
    public $value;

    public function __construct($value = null)
    {
        if (empty($value['key'])) {
            $value['key'] = $value['value'] ?? null;
        }

        $this->value = $value;
    }
}
