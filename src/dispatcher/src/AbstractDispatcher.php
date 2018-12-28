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

namespace Hyperf\Dispatcher;

use Hyperf\Contract\DispatcherInterface;

abstract class AbstractDispatcher implements DispatcherInterface
{
    /**
     * @param array ...$params
     * @return mixed
     */
    public function dispatch(...$params)
    {
        return $this->handle(...$params);
    }
}
