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

namespace HyperfTest\Database\Stubs;

use Psr\EventDispatcher\StoppableEventInterface;

class ModelSavingEventStub implements StoppableEventInterface
{
    public function __construct($model = null)
    {
    }

    public function isPropagationStopped(): bool
    {
        return true;
    }
}
