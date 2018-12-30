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

namespace Hyperf\Event\Contract;

use Psr\EventDispatcher\MessageInterface;

interface MessageListenerInterface
{
    /**
     * @return string[] Returns the events that you want to listen.
     */
    public function listen(): array;

    /**
     * Handler the message event when the event triggered.
     * Notice that this action maybe defered.
     */
    public function process(MessageInterface $event);
}
