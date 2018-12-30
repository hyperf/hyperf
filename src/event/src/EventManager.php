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

namespace Hyperf\Event;

use Psr\EventDispatcher\MessageInterface;
use Psr\EventDispatcher\MessageNotifierInterface;
use Psr\EventDispatcher\TaskProcessorInterface;

class EventManager
{
    /**
     * @var MessageNotifierInterface
     */
    private $notifer;

    /**
     * @var TaskProcessorInterface
     */
    private $processor;

    public function __construct(
        MessageNotifierInterface $notifer,
        TaskProcessorInterface $processor
    ) {
        $this->notifer = $notifer;
        $this->processor = $processor;
    }

    public function trigger($event)
    {
        return $this->processor->process($event);
    }

    public function notify(MessageInterface $event)
    {
        $this->notifer->notify($event);
    }
}
