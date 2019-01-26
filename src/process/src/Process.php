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

use Hyperf\Contract\ProcessInterface;
use Hyperf\Process\Event\AfterProcessHandle;
use Hyperf\Process\Event\BeforeProcessHandle;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Process as SwooleProcess;
use Swoole\Server;

abstract class Process implements ProcessInterface
{
    /**
     * @var string
     */
    public $name = 'process';

    /**
     * @var int
     */
    protected $nums = 1;

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
            $server->addProcess(new SwooleProcess(function (SwooleProcess $process) use ($i) {
                $this->event && $this->event->dispatch(new BeforeProcessHandle($this, $i));
                $this->handle();
                $this->event && $this->event->dispatch(new AfterProcessHandle($this, $i));
            }));
        }
    }
}
