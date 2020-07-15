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
namespace Hyperf\Consul;

interface HealthInterface
{
    public function node($node, array $options = []): ConsulResponse;

    public function checks($service, array $options = []): ConsulResponse;

    public function service($service, array $options = []): ConsulResponse;

    public function state($state, array $options = []): ConsulResponse;
}
