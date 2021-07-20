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
    public $topic = '';

    /**
     * @var string
     */
    public $channel = '';

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var int
     */
    public $nums = 1;

    /**
     * @var string
     */
    public $pool = '';
}
