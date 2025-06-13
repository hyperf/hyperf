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

namespace Hyperf\Process\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ProcessInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeMainServerStart;
use Hyperf\Process\Annotation\Process;
use Hyperf\Process\ProcessManager;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Psr\Container\ContainerInterface;

class BootProcessListener implements ListenerInterface
{
    public function __construct(private ContainerInterface $container, private ConfigInterface $config)
    {
    }

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            BeforeMainServerStart::class,
            MainCoroutineServerStart::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event): void
    {
        /** @var BeforeMainServerStart $event */
        $server = $event->server;
        $serverConfig = $event->serverConfig;

        $serverProcesses = $serverConfig['processes'] ?? [];
        $processes = $this->config->get('processes', []);
        $annotationProcesses = $this->getAnnotationProcesses();

        ProcessManager::setRunning(true);

        // Retrieve the processes have been registered.
        $processes = array_merge($serverProcesses, $processes, ProcessManager::all(), array_keys($annotationProcesses));
        foreach ($processes as $process) {
            if (is_string($process)) {
                $instance = $this->container->get($process);
                if (isset($annotationProcesses[$process])) {
                    foreach ($annotationProcesses[$process] as $property => $value) {
                        if (property_exists($instance, $property) && ! is_null($value)) {
                            $instance->{$property} = $value;
                        }
                    }
                }
            } else {
                $instance = $process;
            }
            if ($instance instanceof ProcessInterface) {
                $instance->isEnable($server) && $instance->bind($server);
            }
        }
    }

    private function getAnnotationProcesses()
    {
        return AnnotationCollector::getClassesByAnnotation(Process::class);
    }
}
