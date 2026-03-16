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

namespace HyperfTest\Command\Command;

use Hyperf\Command\Command;
use Hyperf\Event\EventDispatcher;
use Hyperf\Event\ListenerProvider;

class FooExitCommand extends Command
{
    public function __construct(?string $name = null)
    {
        parent::__construct($name);

        $this->eventDispatcher = new EventDispatcher(
            new ListenerProvider()
        );
    }

    public function handle()
    {
        exit('11xxx');
    }
}
