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
namespace Hyperf\AsyncQueue\Listener;

use Hyperf\AsyncQueue\Event\QueueLength;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;

class ReloadChannelListener implements ListenerInterface
{
    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var string[]
     */
    protected $channels = [
        'timeout',
    ];

    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function listen(): array
    {
        return [
            QueueLength::class,
        ];
    }

    /**
     * @param QueueLength $event
     */
    public function process(object $event)
    {
        if (! $event instanceof QueueLength) {
            return;
        }

        if (! in_array($event->key, $this->channels)) {
            return;
        }

        if ($event->length == 0) {
            return;
        }

        $event->driver->reload($event->key);

        $this->logger->info(sprintf('%s channel reload %d messages to waiting channel success.', $event->key, $event->length));
    }
}
