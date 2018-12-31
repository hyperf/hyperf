<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Event\Contract;

use Psr\EventDispatcher\TaskInterface;

interface TaskListenerInterface extends BaseListenerInterface
{
    /**
     * Handler the task event when the event triggered.
     */
    public function process(TaskInterface $event);
}
