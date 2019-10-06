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

namespace Hyperf\Crontab\Strategy;

use Carbon\Carbon;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Crontab;
use Hyperf\Crontab\LoggerInterface;
use Hyperf\Utils\Coroutine;
use Psr\Container\ContainerInterface;

class Executor
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * @var null|\Hyperf\Crontab\LoggerInterface
     */
    protected $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        if ($container->has(LoggerInterface::class)) {
            $this->logger = $container->get(LoggerInterface::class);
        } elseif ($container->has(StdoutLoggerInterface::class)) {
            $this->logger = $container->get(StdoutLoggerInterface::class);
        }
    }

    public function execute(Crontab $crontab)
    {
        if (! $crontab instanceof Crontab || ! $crontab->getExecuteTime()) {
            return;
        }
        $diff = $crontab->getExecuteTime()->diffInRealSeconds(new Carbon());
        $callback = null;
        switch ($crontab->getType()) {
            case 'callback':
                [$class, $method] = $crontab->getCallback();
                $parameters = $crontab->getCallback()[2] ?? null;
                if ($class && $method && class_exists($class) && method_exists($class, $method)) {
                    $callback = function () use ($class, $method, $parameters, $crontab) {
                        Coroutine::create(function () use ($class, $method, $parameters, $crontab) {
                            try {
                                $result = true;
                                $instance = make($class);
                                if ($parameters && is_array($parameters)) {
                                    $instance->{$method}(...$parameters);
                                } else {
                                    $instance->{$method}();
                                }
                            } catch (\Throwable $throwable) {
                                $result = false;
                            } finally {
                                if ($this->logger) {
                                    if ($result) {
                                        $this->logger->info(sprintf('Crontab task [%s] execute success at %s.', $crontab->getName(), date('Y-m-d H:i:s')));
                                    } else {
                                        $this->logger->error(sprintf('Crontab task [%s] execute failure at %s.', $crontab->getName(), date('Y-m-d H:i:s')));
                                    }
                                }
                            }
                        });
                    };
                }
                break;
            case 'command':
                break;
            case 'eval':
                $callback = function () use ($crontab) {
                    eval($crontab->getCallback());
                };
                break;
        }
        $callback && swoole_timer_after($diff > 0 ? $diff * 1000 : 1, $callback);
    }
}
