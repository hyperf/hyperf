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
namespace Hyperf\Nano\Factory;

use Hyperf\Process\AbstractProcess;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;

class ProcessFactory
{
    public function create(\Closure $closure): AbstractProcess
    {
        $container = ApplicationContext::getContainer();
        return new class($container, $closure) extends AbstractProcess {
            private $closure;

            public function __construct(ContainerInterface $container, \Closure $closure)
            {
                parent::__construct($container);
                $this->closure = $closure;
            }

            public function handle(): void
            {
                call($this->closure);
            }
        };
    }
}
