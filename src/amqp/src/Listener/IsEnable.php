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
namespace Hyperf\Amqp\Listener;

use Hyperf\Contract\ConfigInterface;

trait IsEnable
{
    protected function isEnable(): bool
    {
        if (! $this->container->has(ConfigInterface::class)) {
            return true;
        }

        $config = $this->container->get(ConfigInterface::class);
        return (bool) $config->get('amqp.enable', true);
    }
}
