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
use Hyperf\Crontab\Crontab;
use Hyperf\Utils\Coroutine;
use Psr\Container\ContainerInterface;

class Executor
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function execute(array $data)
    {
        $crontab = $data['data'] ?? null;
        if (! $crontab instanceof Crontab || ! $crontab->getExecuteTime()) {
            return;
        }
        $diff = $crontab->getExecuteTime()->diffInRealSeconds(new Carbon());
        $callback = null;
        switch ($crontab->getType()) {
            case 'callback':
                [$class, $method, $parameters] = $crontab->getCallback();
                if ($class && $method && class_exists($class) && method_exists($class, $method)) {
                    $callback = function () use ($class, $method, $parameters) {
                        Coroutine::create(function () use ($class, $method, $parameters) {
                            $instance = make($class);
                            $instance->{$method}(...$parameters);
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
