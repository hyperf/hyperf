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

class ServiceGovernanceManager
{
    /**
     * @var ServiceGovernanceInterface[]
     */
    protected $governance = [];

    public function register(string $name, ServiceGovernanceInterface $governance)
    {
        $this->governance[$name] = $governance;
    }

    public function get(string $name): ?ServiceGovernanceInterface
    {
        return $this->governance[$name] ?? null;
    }
}
