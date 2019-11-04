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

namespace Hyperf\Retrier;

use Psr\Container\ContainerInterface;
use Swoole\Coroutine\sleep;

class Retrier implements RetrierInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var StrategyInterface
     */
    protected $strategy;

    /**
     * @var int
     */
    protected $retryCount;

    /**
     * @var int
     */
    protected $maxAttempt;

    /**
     * Current backoff time
     * @var float
     */
    protected $timeInteval;

    /**
     * Initial backoff time (ms)
     * @var float
     */
    protected $baseTime;


    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->baseTime = 0.1;
        $this->timeInteval = null;
        $this->retryCount = 0;
        $this->strategy = $container->get(StrategyInterface::class);
    }

    public function sleepTillRetry(): void
    {
        if (!isset($this->timeInteval)) {
            $this->timeInteval = $this->baseTime;
        }
        $this->timeInteval = $this->strategy->calculate($this->timeInteval);
        usleep($this->strategy->calculate($this->timeInteval));
        ++$this->retryCount;
    }

    public function canRetry(): bool
    {
        if ($this->retryCount <= $this->maxAttempt) {
            return true;
        }
        return false;
    }

    public function reset(array $config = [])
    {
        $this->baseTime = data_get($config, 'base_time', 100);
        $this->maxAttempt = data_get($config, 'max_attampt', INF);
    }
}
