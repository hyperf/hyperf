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

namespace Hyperf\Kafka\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_CLASS)]
class Consumer extends AbstractAnnotation
{
    /**
     * @param string|string[] $topic
     */
    public function __construct(
        public string $pool = 'default',
        public array|string $topic = '',
        public ?string $groupId = null,
        public ?string $memberId = null,
        public bool $autoCommit = true,
        public int $nums = 1,
        public bool $enable = true
    ) {
    }
}
