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

class QueueLengthListener implements ListenerInterface
{
    protected array $level = [
        'debug' => 10,
        'info' => 50,
        'warning' => 500,
    ];

    public function __construct(protected StdoutLoggerInterface $logger)
    {
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
    public function process(object $event): void
    {
        $value = 0;
        foreach ($this->level as $level => $value) {
            if ($event->length < $value) {
                $message = sprintf('Queue length of %s is %d.', $event->key, $event->length);
                $this->logger->{$level}($message);
                break;
            }
        }

        if ($event->length >= $value) {
            $this->logger->error(sprintf('Queue length of %s is %d.', $event->key, $event->length));
        }
    }
}
