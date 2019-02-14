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

namespace Hyperf\Process;

use Swoole\Server;
use Swoole\Process as SwooleProcess;
use Hyperf\Contract\ProcessInterface;
use Psr\Container\ContainerInterface;
use Hyperf\Process\Event\AfterProcessHandle;
use Hyperf\Process\Event\BeforeProcessHandle;
use Psr\EventDispatcher\EventDispatcherInterface;

abstract class Process implements ProcessInterface
{
    /**
     * @var string
     */
    public $name = 'process';

    /**
     * @var int
     */
    public $nums = 1;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var null|EventDispatcherInterface
     */
    protected $event;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        if ($container->has(EventDispatcherInterface::class)) {
            $this->event = $container->get(EventDispatcherInterface::class);
        }
    }

    public function bind(Server $server): void
    {
        $num = $this->nums;
        for ($i = 0; $i < $num; ++$i) {
            $process = new SwooleProcess(function (SwooleProcess $process) use ($i) {
                $this->event && $this->event->dispatch(new BeforeProcessHandle($this, $i));
                $this->handle();
                $this->event && $this->event->dispatch(new AfterProcessHandle($this, $i));
            });
            $server->addProcess($process);
        }
    }
}
