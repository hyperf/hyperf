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

namespace Hyperf\Config\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Value extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $key;

    public function __construct($value = null)
    {
        parent::__construct($value);
        $this->bindMainProperty('key', $value);
    }
}
