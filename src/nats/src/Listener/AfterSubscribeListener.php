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

namespace Hyperf\Nats\Listener;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Nats\Event\AfterSubscribe;

class AfterSubscribeListener implements ListenerInterface
{
    public function __construct(protected StdoutLoggerInterface $logger)
    {
    }

    public function listen(): array
    {
        return [
            AfterSubscribe::class,
        ];
    }

    /**
     * @param AfterSubscribe $event
     */
    public function process(object $event): void
    {
        $this->logger->warning(sprintf(
            'NatsConsumer[%s] subscribe timeout. Try again after 1 ms.',
            $event->getConsumer()->getName()
        ));
    }
}
