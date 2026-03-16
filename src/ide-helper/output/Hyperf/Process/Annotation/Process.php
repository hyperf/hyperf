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

namespace Hyperf\Process\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_CLASS)]
class Process extends AbstractAnnotation
{
    public function __construct(?int $nums = null, ?string $name = null, ?bool $redirectStdinStdout = null, ?int $pipeType = null, ?bool $enableCoroutine = null)
    {
    }
}
