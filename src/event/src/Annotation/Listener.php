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
namespace Hyperf\Event\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Listener extends AbstractAnnotation
{
    /**
     * @var int
     */
    public $priority = 1;

    public function __construct($value = null)
    {
        if (isset($value['priority']) && is_numeric($value['priority'])) {
            $this->priority = (int) $value['priority'];
        }
    }
}
