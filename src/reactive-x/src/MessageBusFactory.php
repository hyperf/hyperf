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

namespace Hyperf\ReactiveX;

use Hyperf\ReactiveX\Contract\BroadcasterInterface;
use Psr\Container\ContainerInterface;
use Rx\Subject\Subject;

class MessageBusFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $subject = new Subject();
        $broadcaster = $container->get(BroadcasterInterface::class);
        return new IpcSubject($subject, $broadcaster, 0);
    }
}
