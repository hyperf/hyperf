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

namespace Hyperf\Queue\Event;

use Hyperf\Queue\MessageInterface;

class Event
{
    /**
     * @var MessageInterface
     */
    public $message;

    public function __construct(MessageInterface $message)
    {
        $this->message = $message;
    }
}
