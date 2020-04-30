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

use Hyperf\Command\Command;

class CommandFactory
{
    public function create(string $name, \Closure $closure): Command
    {
        return new class($name, $closure) extends Command {
            private $closure;

            public function __construct(string $name, \Closure $closure)
            {
                parent::__construct($name);
                $this->closure = $closure;
            }

            public function handle()
            {
                call($this->closure);
            }
        };
    }
}
