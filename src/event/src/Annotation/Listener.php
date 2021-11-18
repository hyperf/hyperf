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

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Event\ListenerData;

#[Attribute(Attribute::TARGET_CLASS)]
class Listener extends AbstractAnnotation
{
    public int $priority = ListenerData::DEFAULT_PRIORITY;

    public function __construct(...$value)
    {
        $value = $this->formatParams($value);
        if (is_numeric($value['priority'] ?? null)) {
            $this->priority = (int) $value['priority'];
        }
    }
}
