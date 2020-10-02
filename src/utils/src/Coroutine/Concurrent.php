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
namespace Hyperf\Utils\Coroutine;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Utils\ApplicationContext;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

/**
 * @method bool isFull()
 * @method bool isEmpty()
 * @method array stats()
 * @method int length()
 */
class Concurrent
{
    /**
     * @var Channel
     */
    protected $channel;

    /**
     * @var int
     */
    protected $limit;

    public function __construct(int $limit)
    {
        $this->limit = $limit;
        $this->channel = new Channel($limit);
    }

    public function __call($name, $arguments)
    {
        if (in_array($name, ['isFull', 'isEmpty', 'length', 'stats'])) {
            return $this->channel->{$name}(...$arguments);
        }
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getLength(): int
    {
        return $this->channel->length();
    }

    public function getRunningCoroutineCount(): int
    {
        return $this->getLength();
    }

    public function create(callable $callable): void
    {
        $this->channel->push(true);

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
                $this->channel->pop();
            }
        });
    }
}
