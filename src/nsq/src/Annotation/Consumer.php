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

namespace Hyperf\Nsq\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_CLASS)]
class Consumer extends AbstractAnnotation
{
    public function __construct(
        public string $topic = '',
        public string $channel = '',
        public string $name = '',
        public int $nums = 1,
        public string $pool = ''
    ) {
    }
}
