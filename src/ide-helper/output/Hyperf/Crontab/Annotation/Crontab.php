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

namespace Hyperf\Crontab\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Crontab extends AbstractAnnotation
{
    public function __construct(?string $name = null, string $type = 'callback', ?string $rule = null, ?bool $singleton = null, ?string $mutexPool = null, ?int $mutexExpires = null, ?bool $onOneServer = null, null|array|string $callback = null, ?string $memo = null, array|bool|string $enable = true)
    {
    }
}
