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

namespace Hyperf\Tracer\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class Trace extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $name = '';

    public function __construct($value = null)
    {
        parent::__construct($value);
        if (isset($value['name'])) {
            $this->name = $value['name'];
        }
    }
}
