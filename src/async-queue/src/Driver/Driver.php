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

namespace Hyperf\AsyncQueue\Driver;

use Hyperf\AsyncQueue\Environment;
use Hyperf\AsyncQueue\Event\AfterHandle;
use Hyperf\AsyncQueue\Event\BeforeHandle;
use Hyperf\AsyncQueue\Event\FailedHandle;
use Hyperf\AsyncQueue\Event\RetryHandle;
use Hyperf\AsyncQueue\Exception\InvalidPackerException;
use Hyperf\AsyncQueue\MessageInterface;
use Hyperf\Contract\PackerInterface;
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

    public function __construct(ContainerInterface $container, $config)
    {
        $this->container = $container;
        $this->packer = $container->get($config['packer'] ?? PhpSerializerPacker::class);
        $this->event = $container->get(EventDispatcherInterface::class);

        if (! $this->packer instanceof PackerInterface) {
            throw new InvalidPackerException(sprintf('[Error] %s is not a invalid packer.', $config['packer']));
        }

        if ($concurrent = $config['concurrent'] ?? null) {
            $this->concurrent = new Concurrent($concurrent['limit'] ?? 10, $concurrent['timeout'] ?? 10);
        }
    }

    public function consume(): void
    {
        $this->container->get(Environment::class)->setAsyncQueue(true);

        while (true) {
            [$data, $message] = $this->pop();

            if ($data === false) {
                continue;
            }

            $callback = function () use ($message, $data) {
                try {
                    if ($message instanceof MessageInterface) {
                        $this->event && $this->event->dispatch(new BeforeHandle($message));
                        $message->job()->handle();
                        $this->event && $this->event->dispatch(new AfterHandle($message));
                    }

                    $this->ack($data);
                } catch (\Throwable $ex) {
                    if ($message->attempts() && $this->remove($data)) {
                        $this->event && $this->event->dispatch(new RetryHandle($message, $ex));
                        $this->retry($message);
                    } else {
                        $this->event && $this->event->dispatch(new FailedHandle($message, $ex));
                        $this->fail($data);
                    }
                }
            };

            if ($this->concurrent instanceof Concurrent) {
                $this->concurrent->create($callback);
            } else {
                parallel([$callback]);
            }
        }
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
