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

/**
 * @Annotation
 * @Target({"CLASS"})
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Consumer extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $pool = 'default';

    /**
     * @var string|string[]
     */
    public $topic;

    /**
     * @var null|string
     */
    public $groupId;

    /**
     * @var null|string
     */
    public $memberId;

    /**
     * @var bool
     */
    public $autoCommit = true;

    /**
     * @var int
     */
    public $nums = 1;

    /**
     * @var bool
     */
    public $enable = true;
}
