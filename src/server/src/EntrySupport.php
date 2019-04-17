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

namespace Hyperf\Server;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Server\Entry\EventDispatcher;
use Hyperf\Server\Entry\Logger;
use Hyperf\Server\Exception\RuntimeException;
use Psr\EventDispatcher\EventDispatcherInterface;

trait EntrySupport
{
    public function getEntryInstance(string $name)
    {
        $properties = [
            'dispatcher' => [EventDispatcherInterface::class, EventDispatcher::class],
            'logger' => [StdoutLoggerInterface::class, Logger::class],
        ];

        if (! isset($properties[$name])) {
            throw new RuntimeException(sprintf('Property %s is invalid.', $name));
        }

        [$interface, $default] = $properties[$name];

        if ($this->{$name} instanceof $interface) {
            return $this->{$name};
        }

        if ($this->container->has($interface)) {
            $entry = $this->container->get($interface);
            if ($entry instanceof $interface) {
                return $this->{$name} = $entry;
            }
        }

        return $this->{$name} = make($default);
    }
}
