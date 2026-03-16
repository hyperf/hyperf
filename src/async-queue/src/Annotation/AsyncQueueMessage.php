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

namespace Hyperf\AsyncQueue\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * Don't call the methods with this annotation in async queue environment.
 * Because the execution or delivery of a message depends on whether it is currently in an async queue environment,
 * re delivery in an async queue environment will be treated as a direct execution of the message.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class AsyncQueueMessage extends AbstractAnnotation
{
    public function __construct(
        public string $pool = 'default',
        public int $delay = 0,
        public int $maxAttempts = 0
    ) {
    }
}
