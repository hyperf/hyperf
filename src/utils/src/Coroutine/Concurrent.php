<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Utils\Coroutine;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Coroutine;
use Swoole\Coroutine\Channel;

class Concurrent
{
    /**
     * @var Channel
     */
    protected $channel;

    /**
     * @var float
     */
    protected $timeout;

    public function __construct($size, $timeout = 10.0)
    {
        $this->channel = new Channel($size);
        $this->timeout = $timeout;
    }

    public function length(): int
    {
        return $this->channel->length();
    }

    public function call(callable $callable): void
    {
        while (true) {
            if ($this->channel->push(true, $this->timeout)) {
                break;
            }
        }

        Coroutine::create(function () use ($callable) {
            try {
                $callable();
            } catch (\Throwable $exception) {
                if (ApplicationContext::hasContainer()) {
                    $container = ApplicationContext::getContainer();
                    if ($container->has(StdoutLoggerInterface::class) && $container->has(FormatterInterface::class)) {
                        $logger = $container->get(StdoutLoggerInterface::class);
                        $formatter = $container->get(FormatterInterface::class);
                        $logger->error($formatter->format($exception));
                    }
                }
            } finally {
                $this->channel->pop($this->timeout);
            }
        });
    }
}
