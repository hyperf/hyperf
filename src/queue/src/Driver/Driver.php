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

namespace Hyperf\Queue\Driver;

use Hyperf\Queue\Event\AfterHandle;
use Hyperf\Queue\Event\BeforeHandle;
use Hyperf\Queue\Event\FailedHandle;
use Hyperf\Queue\Event\RetryHandle;
use Hyperf\Queue\Exception\InvalidPackerException;
use Hyperf\Queue\MessageInterface;
use Hyperf\Contract\PackerInterface;
use Hyperf\Queue\Packer\PhpSerializer;
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

    public function __construct(ContainerInterface $container, $config)
    {
        $this->container = $container;
        $this->packer = $container->get($config['packer'] ?? PhpSerializer::class);
        $this->event = $container->get(EventDispatcherInterface::class);

        if (!$this->packer instanceof PackerInterface) {
            throw new InvalidPackerException(sprintf('[Error] %s is not a invalid packer.', $config['packer']));
        }
    }

    public function consume(): void
    {
        while (true) {
            list($data, $message) = $this->pop($this->timeout);

            if ($data === false) {
                continue;
            }

            try {
                if ($message instanceof MessageInterface) {
                    $this->event && $this->event->dispatch(new BeforeHandle($message));
                    $message->job()->handle();
                    $this->event && $this->event->dispatch(new AfterHandle($message));
                }

                $this->ack($data);
            } catch (\Throwable $ex) {
                if ($message->attempts() && $this->remove($data)) {
                    $this->event && $this->event->dispatch(new RetryHandle($message));
                    $this->retry($message);
                } else {
                    $this->event && $this->event->dispatch(new FailedHandle($message));
                    $this->fail($data);
                }
            }
        }
    }

    /**
     * Handle a job again some seconds later.
     *
     * @param MessageInterface $message
     */
    abstract protected function retry(MessageInterface $message): bool;
}
