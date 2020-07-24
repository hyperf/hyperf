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

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class AsyncQueueMessage extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $pool = 'default';

    /**
     * @var int
     */
    public $delay = 0;

    /**
     * @var int
     */
    public $maxAttempts = 0;
}
