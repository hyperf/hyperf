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
namespace Hyperf\Coroutine;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coroutine\Exception\InvalidArgumentException;
use Hyperf\Engine\Channel;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Throwable;

/**
 * @method bool isFull()
 * @method bool isEmpty()
 */
class Concurrent
{
    protected Channel $channel;

    public function __construct(protected int $limit)
    {
        $this->channel = new Channel($limit);
    }

    public function __call($name, $arguments)
    {
        if (in_array($name, ['isFull', 'isEmpty'])) {
            return $this->channel->{$name}(...$arguments);
        }

        throw new InvalidArgumentException(sprintf('The method %s is not supported.', $name));
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function length(): int
    {
        return $this->channel->getLength();
    }

    public function getLength(): int
    {
        return $this->channel->getLength();
    }

    public function getRunningCoroutineCount(): int
    {
        return $this->getLength();
    }

    public function getChannel(): Channel
    {
        return $this->channel;
    }

    public function create(callable $callable): void
    {
        $this->channel->push(true);

        Coroutine::create(function () use ($callable) {
            try {
                $callable();
            } catch (Throwable $exception) {
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
