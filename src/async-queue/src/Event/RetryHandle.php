<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\AsyncQueue\Event;

use Hyperf\AsyncQueue\MessageInterface;
use Throwable;

class RetryHandle extends Event
{
    /**
     * @var Throwable
     */
    protected $throwable;

    public function __construct(MessageInterface $message, Throwable $throwable)
    {
        parent::__construct($message);
        $this->throwable = $throwable;
    }

    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }
}
