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

class DriverManager
{
    /**
     * @var DriverInterface[]
     */
    protected array $drivers = [];

    public function register(string $name, DriverInterface $governance): void
    {
        $this->drivers[$name] = $governance;
    }

    public function get(string $name): ?DriverInterface
    {
        return $this->drivers[$name] ?? null;
    }
}
