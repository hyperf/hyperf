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

namespace Hyperf\Nats\Process;

use Hyperf\Process\AbstractProcess;
use Psr\Container\ContainerInterface;

class ConsumerProcess extends AbstractProcess
{
    /**
     * @var string
     */
    protected $queue = 'default';

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->name = "squeue.{$this->queue}";
        $this->nums = $this->config['processes'] ?? 1;
    }

    public function handle(): void
    {
    }
}
