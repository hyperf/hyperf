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

namespace Hyperf\Command\Concerns;

use Symfony\Component\Console\Input\InputInterface;

trait NullDisableEventDispatcher
{
    public function addDisableDispatcherOption(): void
    {
    }

    public function disableDispatcher(InputInterface $input)
    {
    }
}
