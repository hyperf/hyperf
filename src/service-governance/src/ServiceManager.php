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

namespace Hyperf\ServiceGovernance;

class ServiceManager
{
    protected array $services = [];

    /**
     * Register a service to the manager.
     */
    public function register(string $name, string $path, array $metadata): void
    {
        if (isset($metadata['protocol'])) {
            $this->services[$name][$path][$metadata['protocol']] = $metadata;
        } else {
            $this->services[$name][$path]['default'] = $metadata;
        }
    }

    /**
     * Deregister a service from the manager.
     */
    public function deregister(string $name, ?string $path = null): void
    {
        if ($path) {
            unset($this->services[$name][$path]);
        } else {
            unset($this->services[$name]);
        }
    }

    /**
     * List all services.
     */
    public function all(): array
    {
        return $this->services;
    }
}
