<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Utils\Coroutine;

use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\Utils\Coroutine;

class Timer
{
    /**
     * @var array
     */
    protected $ids = [];

    /**
     * @var int
     */
    protected $lastId = 0;

    /**
     * @param int $ms millisecond
     */
    public function tick(int $ms, callable $handler): int
    {
        $id = $this->getId();
        Coroutine::create(function () use ($ms, $handler, $id) {
            retry(INF, function () use ($ms, $handler, $id) {
                while (true) {
                    // handler worker exit
                    $coordinator = CoordinatorManager::until(Constants::WORKER_EXIT);
                    $workerExited = $coordinator->yield($ms / 1000);
                    if ($workerExited || ! $this->hasId($id)) {
                        break;
                    }

                    $handler($id);
                }
            }, $ms);
        });

        return $id;
    }

    public function after(int $ms, callable $handler)
    {
        $id = $this->getId();
        Coroutine::create(function () use ($ms, $handler, $id) {
            retry(INF, function () use ($ms, $handler, $id) {
                $coordinator = CoordinatorManager::until(Constants::WORKER_EXIT);
                $workerExited = $coordinator->yield($ms / 1000);
                if ($workerExited || ! $this->hasId($id)) {
                    return;
                }

                $handler($id);
            }, $ms);
        });
        return $id;
    }

    public function clear(int $id): bool
    {
        $this->ids[$id] = null;
        return true;
    }

    public function clearAll(): bool
    {
        $this->ids = [];
        return true;
    }

    public function hasId(int $id): bool
    {
        return isset($this->ids[$id]);
    }

    protected function getId(): int
    {
        ++$this->lastId;
        return $this->ids[$this->lastId] = $this->lastId;
    }
}
