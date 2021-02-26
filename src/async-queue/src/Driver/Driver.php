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
namespace Hyperf\AsyncQueue\Driver;

use Hyperf\AsyncQueue\Event\AfterHandle;
use Hyperf\AsyncQueue\Event\BeforeHandle;
use Hyperf\AsyncQueue\Event\FailedHandle;
use Hyperf\AsyncQueue\Event\QueueLength;
use Hyperf\AsyncQueue\Event\RetryHandle;
use Hyperf\AsyncQueue\Exception\InvalidPackerException;
use Hyperf\AsyncQueue\MessageInterface;
use Hyperf\Contract\PackerInterface;
use Hyperf\Process\ProcessManager;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Coroutine\Concurrent;
use Hyperf\Utils\Packer\PhpSerializerPacker;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

abstract class Driver implements DriverInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var PackerInterface
     */
    protected $packer;

    /**
     * @var EventDispatcherInterface
     */
    protected $event;

    /**
     * @var null|Concurrent
     */
    protected $concurrent;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var int
     */
    protected $lengthCheckCount = 500;

    public function __construct(ContainerInterface $container, $config)
    {
        $this->container = $container;
        $this->packer = $container->get($config['packer'] ?? PhpSerializerPacker::class);
        $this->event = $container->get(EventDispatcherInterface::class);
        $this->config = $config;

        if (! $this->packer instanceof PackerInterface) {
            throw new InvalidPackerException(sprintf('[Error] %s is not a invalid packer.', $config['packer']));
        }

        $concurrentLimit = $config['concurrent']['limit'] ?? null;
        if ($concurrentLimit && is_numeric($concurrentLimit)) {
            $this->concurrent = new Concurrent((int) $concurrentLimit);
        }
    }

    public function consume(): void
    {
        $messageCount = 0;
        $maxMessages = Arr::get($this->config, 'max_messages', 0);

        while (ProcessManager::isRunning()) {
            [$data, $message] = $this->pop();

            if ($data === false) {
                continue;
            }

            $callback = $this->getCallback($data, $message);

            if ($this->concurrent instanceof Concurrent) {
                $this->concurrent->create($callback);
            } else {
                parallel([$callback]);
            }

            if ($messageCount % $this->lengthCheckCount === 0) {
                $this->checkQueueLength();
            }

            if ($maxMessages > 0 && $messageCount >= $maxMessages) {
                break;
            }

            ++$messageCount;
        }
    }

    protected function checkQueueLength()
    {
        $info = $this->info();
        foreach ($info as $key => $value) {
            $this->event && $this->event->dispatch(new QueueLength($this, $key, $value));
        }
    }

    protected function getCallback($data, $message): callable
    {
        return function () use ($data, $message) {
            try {
                if ($message instanceof MessageInterface) {
                    $this->event && $this->event->dispatch(new BeforeHandle($message));
                    $message->job()->handle();
                    $this->event && $this->event->dispatch(new AfterHandle($message));
                }

                $this->ack($data);
            } catch (\Throwable $ex) {
                if (isset($message, $data)) {
                    if ($message->attempts() && $this->remove($data)) {
                        $this->event && $this->event->dispatch(new RetryHandle($message, $ex));
                        $this->retry($message);
                    } else {
                        $this->event && $this->event->dispatch(new FailedHandle($message, $ex));
                        $this->fail($data);
                    }
                }
            }
        };
    }

    /**
     * Handle a job again some seconds later.
     */
    abstract protected function retry(MessageInterface $message): bool;

    /**
     * Remove data from reserved queue.
     * @param mixed $data
     */
    abstract protected function remove($data): bool;
}
